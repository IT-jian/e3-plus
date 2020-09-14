<?php


namespace App\Services\Adaptor\Jingdong\Listeners;


use App\Services\Adaptor\Jingdong\Events\JingdongExchangeCreateEvent;
use App\Services\Hub\HubRequestEnum;

class JingdongExchangeCreateListener
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

    public function handle(JingdongExchangeCreateEvent $event)
    {
        $queue = [];
        $stdExchanges = $event->stdExchanges;
        // 状态变更了，需要判断是否需要推送
        foreach ($stdExchanges as $stdExchange) {
            if (in_array($stdExchange['status'], ['10005', 'WAIT_SELLER_CONFIRM_GOODS'])) { // 待收货
                if (cutoverTrade($stdExchange['tid'], $stdExchange['platform'])) {
                    $formatQueue = $this->formatQueue($stdExchange['dispute_id'], HubRequestEnum::EXCHANGE_CREATE_EXTEND);
                } else {
                    $formatQueue = $this->formatQueue($stdExchange['dispute_id'], HubRequestEnum::EXCHANGE_CREATE);
                }
                $queue[] = $formatQueue;
                if (!empty($stdExchange['buyer_logistic_no'])) {
                    $queue[] = $this->formatQueue($stdExchange['dispute_id'], HubRequestEnum::EXCHANGE_RETURN_LOGISTIC_MODIFY);
                }
            }
        }

        if ($queue) {
            $this->pushQueue($queue);
        }
    }
}
