<?php


namespace App\Services\Adaptor\Jingdong\Listeners;


use App\Services\Adaptor\Jingdong\Events\JingdongTradeUpdateEvent;
use App\Services\Hub\HubRequestEnum;

class JingdongStepTradeUpdateListener
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

    /**
     * @param JingdongTradeUpdateEvent $event
     *
     * @see https://open.jd.com/home/home#/doc/common?listId=764
     */
    public function handle(JingdongTradeUpdateEvent $event)
    {
        $queue = [];
        $stdTrade = $event->stdTrade;
        $existTrade = $event->existTrade;
        // 状态变更了，需要判断是否需要推送

        if (!empty($stdTrade) && $stdTrade['status'] != $existTrade['order_status']) { // 0 未付定金/全款, 1 定金已付, 2 尾款已付, 3 全款支付完成,
            if (in_array($stdTrade['status'], [1])) { // 支付定金
                $formatQueue = $this->formatQueue($stdTrade['tid'], HubRequestEnum::STEP_TRADE_CREATE);
                $queue[] = $formatQueue;
            } else if (in_array($stdTrade['status'], [2])) { // 支付尾款
                $formatQueue = $this->formatQueue($stdTrade['tid'], HubRequestEnum::STEP_TRADE_PAID);
                $queue[] = $formatQueue;
            }
        }

        if ($queue) {
            $this->pushQueue($queue);
        }
    }
}