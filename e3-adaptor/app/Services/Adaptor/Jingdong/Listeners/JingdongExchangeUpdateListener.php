<?php


namespace App\Services\Adaptor\Jingdong\Listeners;


use App\Services\Adaptor\Jingdong\Events\JingdongExchangeUpdateEvent;
use App\Services\Hub\HubRequestEnum;

class JingdongExchangeUpdateListener
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

    public function handle(JingdongExchangeUpdateEvent $event)
    {
        $queue = [];
        $stdExchange = $event->stdExchange;
        $existExchange = $event->existExchange;
        if (!empty($stdExchange) && $stdExchange['status'] != $existExchange['status']) { // 状态变更
            if (in_array($stdExchange['status'], ['10005', 'WAIT_SELLER_CONFIRM_GOODS'])) { // 待收货
                $formatQueue = $this->formatQueue($stdExchange['dispute_id'], HubRequestEnum::EXCHANGE_CREATE);
                $queue[] = $formatQueue;
            }
            if (in_array($stdExchange['status'], ['10011', 'CLOSED'])) { // 取消退单
                $formatQueue = $this->formatQueue($stdExchange['dispute_id'], HubRequestEnum::EXCHANGE_CANCEL);
                $queue[] = $formatQueue;
            }
        }
        if ($stdExchange['buyer_logistic_no'] != $existExchange['buyer_logistic_no']) {
            $queue[] = $this->formatQueue($stdExchange['dispute_id'], HubRequestEnum::EXCHANGE_RETURN_LOGISTIC_MODIFY);
        }
        if ($queue) {
            $this->pushQueue($queue);
        }
    }
}
