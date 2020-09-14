<?php


namespace App\Services\Adaptor\Jingdong\Listeners;


use App\Services\Adaptor\Jingdong\Events\JingdongRefundUpdateEvent;
use App\Services\Hub\HubRequestEnum;

class JingdongRefundUpdateListener
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

    public function handle(JingdongRefundUpdateEvent $event)
    {
        $queue = [];
        $stdRefund = $event->stdRefund;
        $existRefund = $event->existRefund;
        if (!empty($stdRefund) && $stdRefund['status'] != $existRefund['status']) { // 状态变更
            if (in_array($stdRefund['status'], ['10005', 'WAIT_SELLER_CONFIRM_GOODS'])) { // 待收货
                $formatQueue = $this->formatQueue($stdRefund['refund_id'], HubRequestEnum::REFUND_RETURN_CREATE);
                $queue[] = $formatQueue;
            }
            if (in_array($stdRefund['status'], ['10011', 'CLOSED'])) { // 取消退单
                $formatQueue = $this->formatQueue($stdRefund['refund_id'], HubRequestEnum::REFUND_RETURN_CANCEL);
                $queue[] = $formatQueue;
            }
        }
        if ($existRefund['sid'] != $stdRefund['sid']) { // 退货物流更新
            $formatQueue = $this->formatQueue($stdRefund['refund_id'], HubRequestEnum::REFUND_RETURN_LOGISTIC_MODIFY);
            $queue[] = $formatQueue;
        }
        if ($queue) {
            $this->pushQueue($queue);
        }
    }
}
