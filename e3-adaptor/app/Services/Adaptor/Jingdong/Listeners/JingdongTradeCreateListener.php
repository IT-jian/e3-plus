<?php


namespace App\Services\Adaptor\Jingdong\Listeners;


use App\Services\Adaptor\Jingdong\Downloader\OrderSplitAmountDownloader;
use App\Services\Adaptor\Jingdong\Events\JingdongTradeCreateEvent;
use App\Services\Adaptor\Jingdong\Jobs\OrderSplitAmountDownloadJob;
use App\Services\Hub\HubRequestEnum;

class JingdongTradeCreateListener
{
    use JingdongPushQueueTrait;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function handle(JingdongTradeCreateEvent $event)
    {
        $queue = $splitOrderShopMap = [];
        $stdTrades = $event->stdTrades;
        foreach ($stdTrades as $stdTrade) {
            if (!empty($stdTrade) && in_array($stdTrade['status'], ['WAIT_SELLER_STOCK_OUT'])) {
                if (cutoverTrade($stdTrade['tid'], $stdTrade['platform'])) {
                    $queue[] = $this->formatQueue($stdTrade['tid'], HubRequestEnum::TRADE_CREATE, 1, ['cutover' => '1']);
                } else {
                    $queue[] = $this->formatQueue($stdTrade['tid'], HubRequestEnum::TRADE_CREATE);
                }
                $splitOrderShopMap[$stdTrade['shop_code']][] = $stdTrade['tid'];
            }
        }

        if ($queue) {
            foreach ($splitOrderShopMap as $shopCode => $orderIds) {
                foreach (array_chunk($orderIds, 50) as $chunkIds) {// JOB 拆分
                    // 少于50个的直接开始批量执行
                    try {
                        $params = ['shop_code' => $shopCode, 'order_ids' => $chunkIds];
                        app(OrderSplitAmountDownloader::class)->download($params);
                    } catch (\Exception $e) {
                        dispatch(new OrderSplitAmountDownloadJob($params));
                    }
                }
            }
            $this->pushQueue($queue);
        }
    }
}
