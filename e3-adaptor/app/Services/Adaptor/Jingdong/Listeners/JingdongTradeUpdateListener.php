<?php


namespace App\Services\Adaptor\Jingdong\Listeners;


use App\Models\JingdongRefundApply;
use App\Services\Adaptor\Jingdong\Events\JingdongTradeUpdateEvent;
use App\Services\Adaptor\Jingdong\Jobs\JingdongRefundApplyDownloadJob;
use App\Services\Hub\HubRequestEnum;

class JingdongTradeUpdateListener
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

    public function handle(JingdongTradeUpdateEvent $event)
    {
        $queue = [];
        $stdTrade = $event->stdTrade;
        $existTrade = $event->existTrade;
        // 状态变更了，需要判断是否需要推送
        if (!empty($stdTrade) && $stdTrade['status'] != $existTrade['status']) {
            if (in_array($stdTrade['status'], ['WAIT_SELLER_STOCK_OUT'])) {
                if (cutoverTrade($stdTrade['tid'], $stdTrade['platform'])) {
                    $queue[] = $this->formatQueue($stdTrade['tid'], HubRequestEnum::TRADE_CREATE, 1, ['cutover' => '1']);
                } else {
                    $queue[] = $this->formatQueue($stdTrade['tid'], HubRequestEnum::TRADE_CREATE);
                }
            }
        }
        if ($queue) {
            $this->pushQueue($queue);
        }
    }
}
