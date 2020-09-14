<?php


namespace App\Services\Adaptor\Jingdong\Listeners;


use App\Services\Adaptor\Jingdong\Events\JingdongTradeBatchCreateEvent;
use App\Services\Adaptor\Jingdong\Jobs\OrderSplitAmountDownloadJob;
use App\Services\Adaptor\Jobs\TradeCreatePushJob;
use App\Services\Hub\HubRequestEnum;

class JingdongTradeBatchCreateListener
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

    public function handle(JingdongTradeBatchCreateEvent $event)
    {
        $queue = $splitOrderShopMap = [];
        $stdTrades = $event->stdTrades;
        foreach ($stdTrades as $stdTrade) {
            // WAIT_SELLER_STOCK_OUT 等待出库
            // WAIT_GOODS_RECEIVE_CONFIRM 等待确认收货
            // WAIT_SELLER_DELIVERY 等待发货
            // PAUSE 暂停
            // FINISHED_L 完成
            // TRADE_CANCELED 取消
            // LOCKED 已锁定
            // POP_ORDER_PAUSE pop业务暂停
            if (!empty($stdTrade) && in_array($stdTrade['status'], ['WAIT_SELLER_DELIVERY'])) {
                if (cutoverTrade($stdTrade['tid'], $stdTrade['platform'])) {
                    $queue[] = $this->formatQueue($stdTrade['tid'], HubRequestEnum::TRADE_CREATE, 1, ['cutover' => '1']);
                } else {
                    $queue[] = $this->formatQueue($stdTrade['tid'], HubRequestEnum::TRADE_CREATE);
                }
                $splitOrderShopMap[$stdTrade['shop_code']][] = $stdTrade['tid'];
            }
        }
        if ($queue) {
            // 触发查询商品金额分摊信息
            foreach ($splitOrderShopMap as $shopCode => $orderIds) {
                foreach (array_chunk($orderIds, 50) as $chunkIds) {
                    $params = ['shop_code' => $shopCode, 'order_ids' => $chunkIds];
                    dispatch(new OrderSplitAmountDownloadJob($params));
                }
            }

            $this->pushQueue($queue);
        }
    }
}
