<?php


namespace App\Services\Adaptor\Jingdong\Listeners;


use App\Services\Adaptor\Jingdong\Events\JingdongTradeCreateEvent;
use App\Services\Hub\HubRequestEnum;

class JingdongStepTradeCreateListener
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
        $queue = [];
        $stdTrades = $event->stdTrades;
        foreach ($stdTrades as $stdTrade) {
            if (!empty($stdTrade) && in_array($stdTrade['status'], [1, 2, 3])) {
                $queue[] = $this->formatQueue($stdTrade['tid'], HubRequestEnum::STEP_TRADE_CREATE);
            }
        }
        if ($queue) {
            $this->pushQueue($queue);
        }
    }
}