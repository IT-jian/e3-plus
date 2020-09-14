<?php


namespace App\Console\Commands\Initial;


use App\Models\JingdongRefund;
use App\Models\Sys\Shop;
use App\Services\Adaptor\Jingdong\Api\Refund;
use App\Services\Adaptor\Jingdong\Jobs\RefundDownloadJob;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use League\Csv\Writer;

class JingdongServiceDownloadApiCommand extends Command
{
    protected $signature = 'adaptor:jingdong:service_download_api
                            {--shop_code= : The names of the shop to download}
                            {--status= : 不支持状态，自动过滤 10005}
                            {--create_from= : 下载开始时间 yyy-mm-dd hh:ii:ss}
                            {--create_to= : 下载结束时间 yyy-mm-dd hh:ii:ss}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '从京东API下载指定创建时间范围内的[等待卖家收货]的服务单';

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
            $status = '10005';
        }
        $shopCodes = [];
        if ($this->hasOption('shop_code') && !empty($this->option('shop_code'))) {
            $shopCodes[] = $this->option('shop_code');
        } else {
            $shops = Shop::available('jingdong')->get()->toArray();
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

        $filePath = $this->getExportFile(Carbon::parse($from)->format('YmdHi')."-" . Carbon::parse($to)->format('YmdHi'));
        $csv = Writer::createFromPath($filePath, 'w+');
        $csv->insertOne([Writer::BOM_UTF8 . 'service_id', 'order_id', 'service status', 'customer expect', 'created', 'shopCode']);

        $pageSize = 50;
        foreach ($shopCodes as $shopCode) {
            $shop = Shop::getShopByCode($shopCode);
            foreach ($timeRange as $time) {
                $page = 1;
                $start = Carbon::createFromTimestamp($time['start'])->toDateTimeString();
                $end = Carbon::createFromTimestamp($time['end'])->toDateTimeString();

                $where = [
                    'page'           => 1,
                    'page_size'      => $pageSize,
                    'start_modified' => $start,
                    'end_modified'   => $end,
                ];

                $refundServer = new Refund($shop);
                $total = $refundServer->count($where);

                $this->info("shop:{$shopCode} {$start} - {$end} found {$total}");

                if ($total) {
                    $totalPage = (int)ceil($total / $pageSize);

                    $bar = $this->output->createProgressBar($totalPage);
                    $bar->start();
                    foreach (range(1, $totalPage) as $page) {
                        $bar->advance();
                        $where['page'] = $page;
                        $refundTrades = $refundServer->page($where);
                        $serviceIds = $exportData = [];
                        foreach ($refundTrades as $refundTrade) {
                            if ($status != $refundTrade['serviceStatus']) {
                                continue;
                            }
                            $serviceIds[] = $refundTrade['serviceId'];
                            $exportData[] = [
                                $refundTrade['serviceId'], $refundTrade['orderId'], $refundTrade['serviceStatusName'],$refundTrade['customerExpectName'], Carbon::createFromTimestampMs($refundTrade['applyTime'])->toDateTimeString(), $shopCode
                            ];
                        }

                        if ($exportData) {
                            $csv->insertAll($exportData);
                        }

                        // 已经存在的不再处理
                        $exists = JingdongRefund::whereIn('service_id', $serviceIds)->get();
                        if ($exists->isNotEmpty()) {
                            $serviceIds = array_diff($serviceIds, $exists->pluck('service_id')->toArray());
                        }
                        if (empty($serviceIds)) {
                            continue;
                        }
                        // 抛出任务
                        foreach ((array)$refundTrades as $refundTrade) {
                            if (!in_array($refundTrade['serviceId'], $serviceIds)) {
                                continue;
                            }
                            $refundTrade['shop'] = $shop;
                            dispatch(new RefundDownloadJob($refundTrade));
                        }
                    }
                    $bar->finish();
                }
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
        $prefix = 'init_service_api_download';

        $string = Carbon::now()->format('YmdHi');

        return $prefix . '_' . $fileName. '_' .$string . '_.csv';
    }
}
