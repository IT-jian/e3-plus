<?php


namespace App\Console\Commands\Initial;


use App\Facades\JosClient;
use App\Models\Sys\Shop;
use App\Services\Adaptor\Jingdong\Jobs\JingdongTradeBatchTransferJob;
use App\Services\Adaptor\Jingdong\Jobs\TradeApiBatchDownloadJob;
use App\Services\Platform\Jingdong\Client\Jos\Request\PopOrderSearchRequest;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use League\Csv\Writer;

class JingdongTradeDownloadApiCommand extends Command
{
    protected $signature = 'adaptor:jingdong:trade_download_api
                            {--shop_code= : The names of the shop to download}
                            {--status= : 多订单状态可以用英文逗号隔开,请不要使用数字，请用英文逗号拼接英文状态传递给jos系统。 1）WAIT_SELLER_STOCK_OUT 等待出库 2）WAIT_GOODS_RECEIVE_CONFIRM 等待确认收货 3）WAIT_SELLER_DELIVERY等待发货（只适用于海外购商家，含义为“等待境内发货”标签下的订单,非海外购商家无需使用） 4) PAUSE 暂停（loc订单可通过此状态获取） 5）FINISHED_L 完成 6）TRADE_CANCELED 取消 7）LOCKED 已锁定 8）POP_ORDER_PAUSE pop业务暂停，如3c号卡/履约/黄金 可传此状态。}
                            {--create_from= : 下载开始时间 yyy-mm-dd hh:ii:ss}
                            {--create_to= : 下载结束时间 yyy-mm-dd hh:ii:ss}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '从京东API下载指定创建时间范围内的[等待卖家发货]的订单';

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
            $status = 'WAIT_SELLER_STOCK_OUT';
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

        krsort($timeRange);
        $filePath = $this->getExportFile(Carbon::parse($from)->format('YmdHi')."-" . Carbon::parse($to)->format('YmdHi'));
        $csv = Writer::createFromPath($filePath, 'w+');
        $csv->insertOne([Writer::BOM_UTF8 . 'orderId', 'orderStartTime', 'orderStateRemark', 'shopCode']);

        $bar = $this->output->createProgressBar(count($timeRange)*count($shopCodes));
        $bar->start();

        $pageSize = '50';
        foreach ($shopCodes as $shopCode) {
            $shop = Shop::getShopByCode($shopCode);
            foreach ($timeRange as $time) {
                $bar->advance();
                $page = 1;
                $start = Carbon::createFromTimestamp($time['start'])->toDateTimeString();
                $end = Carbon::createFromTimestamp($time['end'])->toDateTimeString();
                $request = new PopOrderSearchRequest();
                $request->setOptionalFields('orderId,orderStartTime,orderStateRemark,orderState');
                $request->setOrderState($status);
                $request->setStartDate($start);
                $request->setEndDate($end);
                $request->setPageSize($pageSize);
                $request->setDateType('1');
                $this->info("shop:{$shopCode} {$start} - {$end}");
                $totalPage = 0;
                do {
                    $exportData = $orderIds = [];
                    $request->setPage((string)$page);
                    $response = JosClient::shop($shopCode)->execute($request);
                    $trades = data_get($response, 'jingdong_pop_order_search_responce.searchorderinfo_result.orderInfoList', []);
                    $total = data_get($response, 'jingdong_pop_order_search_responce.searchorderinfo_result.orderTotal', 0);
                    if (1 == $page) {
                        $totalPage = ceil($total / $pageSize);
                        $this->info("shop:{$shopCode} total page:{$totalPage}");
                    }
                    if ($totalPage > 5) {
                        if (1 == $page) {
                            $pageBar = $this->output->createProgressBar($totalPage);
                            $pageBar->start();
                        }
                        $pageBar->advance();
                        if ($totalPage == $page) {
                            $pageBar->finish();
                        }
                    }
                    if (!empty($trades)) {
                        foreach ($trades as $trade) {
                            $orderIds[] = $trade['orderId'];
                            $exportData[] = [
                                $trade['orderId'], $trade['orderStartTime'],$trade['orderState'], $shopCode
                            ];
                        }
                        if ($exportData) {
                            $csv->insertAll($exportData);
                        }
                        $key = 'trade api download ' . $shopCode;
                        $params = ['order_ids' => $orderIds, 'shop_code' => $shopCode, 'platform' => 'jingdong', 'key' => $key];
                        dispatch((new TradeApiBatchDownloadJob($params))->chain(
                            [
                                new JingdongTradeBatchTransferJob(['order_ids' => $orderIds, 'key' => $key]),
                            ]));
                    }
                    $page++;
                } while ($page <= $totalPage);
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
        $prefix = 'init_trade_api_download';

        $string = Carbon::now()->format('YmdHi');

        return $prefix . '_' . $fileName. '_' .$string . '_.csv';
    }
}
