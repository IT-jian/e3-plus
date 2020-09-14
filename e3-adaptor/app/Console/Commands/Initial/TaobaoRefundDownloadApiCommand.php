<?php


namespace App\Console\Commands\Initial;


use App\Facades\TopClient;
use App\Models\Sys\Shop;
use App\Services\Adaptor\Taobao\Jobs\RefundApiBatchDownloadJob;
use App\Services\Adaptor\Taobao\Jobs\RefundBatchTransferJob;
use App\Services\Adaptor\Taobao\Jobs\TaobaoTradeBatchTransferJob;
use App\Services\Adaptor\Taobao\Jobs\TradeApiBatchDownloadJob;
use App\Services\Platform\Taobao\Client\Top\Request\RefundsApplyGetRequest;
use App\Services\Platform\Taobao\Client\Top\Request\RefundsReceiveGetRequest;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use League\Csv\Writer;

class TaobaoRefundDownloadApiCommand extends Command
{
    protected $signature = 'adaptor:taobao:refund_download_api
                            {--shop_code= : The names of the shop to download}
                            {--status= : WAIT_SELLER_AGREE(买家已经申请退款，等待卖家同意) WAIT_BUYER_RETURN_GOODS(卖家已经同意退款，等待买家退货) WAIT_SELLER_CONFIRM_GOODS(买家已经退货，等待卖家确认收货) SELLER_REFUSE_BUYER(卖家拒绝退款) CLOSED(退款关闭) SUCCESS(退款成功)}
                            {--create_from= : 下载开始时间 yyy-mm-dd hh:ii:ss}
                            {--create_to= : 下载结束时间 yyy-mm-dd hh:ii:ss}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '从淘宝API[买家已经退货，等待卖家确认收货]的退货单';

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

        if ($this->hasOption('status') && !empty($this->option('status'))) {
            $status = $this->option('status');
        } else {
            $status = 'WAIT_SELLER_CONFIRM_GOODS';
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

        $filePath = $this->getExportFile($status);
        $csv = Writer::createFromPath($filePath, 'w+');
        $csv->insertOne([Writer::BOM_UTF8 . 'refund_id', 'status', 'created', 'shop_code', 'tid']);

        $bar = $this->output->createProgressBar(count($timeRange)*count($shopCodes));
        $bar->start();

        foreach ($shopCodes as $shopCode) {
            $bar->advance();
            $page = 1;
            $pageSize = 100;
            foreach ($timeRange as $time) {
                $start = Carbon::createFromTimestamp($time['start'])->toDateTimeString();
                $end = Carbon::createFromTimestamp($time['end'])->toDateTimeString();
                $this->info("shop:{$shopCode} {$start} - {$end}");
                do {
                    $request = new RefundsReceiveGetRequest();
                    $request->setFields('refund_id,status,tid,created');
                    $request->setStatus($status);
                    $request->setPageSize($pageSize);
                    $request->setStartModified($start);
                    $request->setEndModified($end);
                    $request->setUseHasNext('true');
                    $request->setPageNo((string)$page);
                    $response = TopClient::shop($shopCode)->execute($request);
                    $refunds = data_get($response, 'refunds_receive_get_response.refunds.refund', []);
                    $this->info("shop:{$shopCode} page:{$page} found:" . count($refunds));
                    if (!empty($refunds)) {
                        $refundIds = $exportData = $tids = [];
                        foreach ($refunds as $refund) {
                            $refundIds[] = $refund['refund_id'];
                            $tids[] = $refund['tid'];
                            $exportData[] = [
                                $refund['refund_id'], $refund['status'], $refund['created'], $shopCode, $refund['tid']
                            ];
                        }
                        if ($exportData) {
                            $csv->insertAll($exportData);
                        }
                        // 先下载订单 再下载退单
                        $key = 'refund api download ' . $shopCode;
                        $params = ['tids' => $tids, 'shopCode' => $shopCode, 'platform' => 'taobao', 'key' => $key];
                        dispatch((new RefundApiBatchDownloadJob(['refund_ids' => $refundIds, 'shopCode' => $shopCode, 'platform' => 'taobao', 'key' => $key]))->chain(
                            [
                                new RefundBatchTransferJob(['refund_ids' => $refundIds, 'key' => $key])
                            ]));
                    }
                    $hasNext = data_get($response, 'refunds_receive_get_response.has_next', false);
                    $this->info("shop:{$shopCode} page: {$page}");
                    $page++;
                } while ($hasNext);
            }
        }
        $bar->finish();
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
        $prefix = 'init_refund_api_download';

        $string = Carbon::now()->format('YmdHi');

        return $prefix . '_' . $fileName. '_' .$string . '_.csv';
    }
}
