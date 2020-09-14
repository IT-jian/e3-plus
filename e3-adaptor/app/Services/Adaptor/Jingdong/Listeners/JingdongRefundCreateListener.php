<?php


namespace App\Services\Adaptor\Jingdong\Listeners;


use App\Services\Adaptor\Jingdong\Events\JingdongRefundCreateEvent;
use App\Services\Hub\HubRequestEnum;

class JingdongRefundCreateListener
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

    public function handle(JingdongRefundCreateEvent $event)
    {
        $queue = [];
        $stdRefunds = $event->stdRefunds;
        // 状态变更了，需要判断是否需要推送
        foreach ($stdRefunds as $stdRefund) {
            if (in_array($stdRefund['status'], ['10005', 'WAIT_SELLER_CONFIRM_GOODS'])) { // 待收货
                if (cutoverTrade($stdRefund['tid'], $stdRefund['platform'])) {
                    $formatQueue = $this->formatQueue($stdRefund['refund_id'], HubRequestEnum::REFUND_RETURN_CREATE_EXTEND);
                } else {
                    $formatQueue = $this->formatQueue($stdRefund['refund_id'], HubRequestEnum::REFUND_RETURN_CREATE);
                }
                $queue[] = $formatQueue;
                if (!empty($stdRefund['sid'])) {
                    $queue[] = $this->formatQueue($stdRefund['refund_id'], HubRequestEnum::REFUND_RETURN_LOGISTIC_MODIFY);
                }
            }
        }

        if ($queue) {
            $this->pushQueue($queue);
        }
    }
}
