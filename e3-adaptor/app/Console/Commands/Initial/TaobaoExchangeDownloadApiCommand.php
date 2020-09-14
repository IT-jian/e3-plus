<?php


namespace App\Console\Commands\Initial;


use App\Facades\TopClient;
use App\Models\Sys\Shop;
use App\Services\Adaptor\Taobao\Jobs\BatchDownload\ExchangeBatchDownloadJob;
use App\Services\Adaptor\Taobao\Jobs\TaobaoTradeBatchTransferJob;
use App\Services\Adaptor\Taobao\Jobs\TradeApiBatchDownloadJob;
use App\Services\Platform\Taobao\Client\Top\Request\ExchangeReceiveGetRequest;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use League\Csv\Writer;

class TaobaoExchangeDownloadApiCommand extends Command
{
    protected $signature = 'adaptor:taobao:exchange_download_api
                            {--shop_code= : The names of the shop to download}
                            {--status= : 换货待处理(1), 待买家退货(2), 买家已退货，待收货(3), 换货关闭(4), 换货成功(5), 待买家修改(6), 待发出换货商品(12), 待买家收货(13), 请退款(14)}
                            {--create_from= : 下载开始时间 yyy-mm-dd hh:ii:ss}
                            {--create_to= : 下载结束时间 yyy-mm-dd hh:ii:ss}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '从淘宝API下载指定创建时间范围内的[买家已退货，待收货]的换货单';

    public function handle()
    {
        if ($this->hasOption('create_from') && !empty($this->option('create_from'))) {
            $from = $this->option('create_from');
        } else {
            $from = Carbon::now()->subWeek()->toDateTimeString();
        }

        if ($this->hasOption('create_to') && !empty($this->option('create_to'))) {
            $to = $this->option('create_to');
        } else {
            $to = Carbon::now()->toDateTimeString();
        }

        $this->info('create_from' . $from);
        $this->info('create_to' . $to);

        if ($this->hasOption('status') && !empty($this->option('status'))) {
            $status = $this->option('status');
        } else {
            $status = '3';
        }
        $shopCodes = [];
        if ($this->hasOption('shop_code') && !empty($this->option('shop_code'))) {
            $shopCodes[] = $this->option('shop_code');
        } else {
            $shops = Shop::available('taobao')->get()->toArray();
            foreach ($shops as $shop) {
                $shopCodes[] = $shop['code'];
            }
        }
        if (empty($shopCodes)) {
            $this->info('empty shop codes');

            return;
        }

        // 如果超过了1天，则每1天为一个分段查询，避免分页过大
        $start = strtotime($from);
        $end = strtotime($to);
        do {
            $endTemp = $start + 24*60*60;
            if ($endTemp > $end) {
                $endTemp = $end+1;
            }
            $timeRange[] = [
                'start' => $start,
                'end' => $endTemp,
            ];
            $start = $endTemp;
        } while ($end > $start);

        krsort($timeRange);

        $filePath = $this->getExportFile(Carbon::parse($from)->format('YmdHi')."-" . Carbon::parse($to)->format('YmdHi'));
        $csv = Writer::createFromPath($filePath, 'w+');
        $csv->insertOne([Writer::BOM_UTF8 . 'refund_id', 'status', 'created', 'shop_code', 'tid']);

        $bar = $this->output->createProgressBar(count($timeRange)*count($shopCodes));
        $bar->start();
        foreach ($shopCodes as $shopCode) {
            foreach ($timeRange as $time) {
                $bar->advance();
                $page = 1;
                $pageSize = 50;

                $start = Carbon::createFromTimestamp($time['start'])->toDateTimeString();
                $end = Carbon::createFromTimestamp($time['end'])->toDateTimeString();

                $request = new ExchangeReceiveGetRequest();
                $fields = 'dispute_id,alipay_no,status,modified,created';
                $request = $request->setFields($fields);
                $request->setPageSize($pageSize);
                $request->setStartCreatedTime($start);
                $request->setEndCreatedTime($end);
                $request->setDisputeStatusArray($status);
                $this->info("shop:{$shopCode} {$start} - {$end}");
                do {
                    $request->setPageNo((string)$page);
                    $response = TopClient::shop($shopCode)->execute($request);
                    $exchanges = data_get($response, 'tmall_exchange_receive_get_response.results.exchange', []);
                    $this->info("shop:{$shopCode} page:{$page} found:" . count($exchanges));
                    if (!empty($exchanges)) {
                        $disputeIds = $exportData = $tids = [];
                        foreach ($exchanges as $exchange) {
                            $disputeIds[] = $exchange['dispute_id'];
                            $tids[] = $exchange['alipay_no'];
                            $exportData[] = [
                                $exchange['dispute_id'], $exchange['status'], $exchange['created'], $shopCode, $exchange['alipay_no']
                            ];
                        }
                        if ($exportData) {
                            $csv->insertAll($exportData);
                        }
                        // 先下载订单 再下载退单
                        $key = 'exchange api download ' . $shopCode;
                        $params = ['tids' => $tids, 'shopCode' => $shopCode, 'platform' => 'taobao', 'key' => $key];
                        dispatch(new ExchangeBatchDownloadJob(['dispute_ids' => $disputeIds, 'shop_code' => $shopCode, 'key' => $key]));
                    }

                    $total = data_get($response, 'tmall_exchange_receive_get_response.total_results', 0);
                    $page++;
                } while (($page-1) * $pageSize < $total );
            }
        }
    }

    public function getExportFile($fileName)
    {
        $fileName = $this->getFileName($fileName);

        if (!File::isDirectory(storage_path('adaptor_export/'))) {
            File::makeDirectory(storage_path('adaptor_export/'));
        }

        return storage_path('adaptor_export/') . $fileName;
    }

    public function getFileName($fileName)
    {
        $prefix = 'init_exchange_api_download';

        $string = Carbon::now()->format('YmdHi');

        return $prefix . '_' . $fileName. '_' .$string . '_.csv';
    }
}
