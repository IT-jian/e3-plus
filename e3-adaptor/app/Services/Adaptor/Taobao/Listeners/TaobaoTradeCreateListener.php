<?php


namespace App\Services\Adaptor\Taobao\Listeners;

use App\Services\Adaptor\Jobs\CancelTradeAfterCreatePushJob;
use App\Services\Adaptor\Taobao\Events\TaobaoTradeCreateEvent;
use App\Services\Hub\HubRequestEnum;

/**
 * Class TaobaoTradeCreateListener
 * @package App\Services\Adaptor\Taobao\Listener
 *
 * @author linqihai
 * @since 2020/1/12 13:44
 */
class TaobaoTradeCreateListener
{
    use TaobaoPushQueueTrait;

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
     * Handle the event.
     *
     * @param  TaobaoTradeCreateEvent  $event
     * @return void
     */
    public function handle(TaobaoTradeCreateEvent $event)
    {
        $queue = [];
        $stdTrades = $event->stdTrades;
        foreach ($stdTrades as $stdTrade) {
            if (!empty($stdTrade) && in_array($stdTrade['status'], ['WAIT_SELLER_SEND_GOODS']) && 'step' != $stdTrade['type']) {
                if (cutoverTrade($stdTrade['tid'], $stdTrade['platform'])) {
                    $queue[] = $this->formatQueue($stdTrade['tid'], HubRequestEnum::TRADE_CREATE, 1, ['cutover' => '1']);
                } else {
                    $queue[] = $this->formatQueue($stdTrade['tid'], HubRequestEnum::TRADE_CREATE);
                }
            } else if (!empty($stdTrade) && 'step' == $stdTrade['type']) {
                // 预售订单支付定金
                if ('WAIT_BUYER_PAY' == $stdTrade['status'] && 'FRONT_PAID_FINAL_NOPAID' == $stdTrade['step_trade_status']) {
                    $queue[] = $this->formatQueue($stdTrade['tid'], HubRequestEnum::STEP_TRADE_CREATE);
                } else if ('WAIT_SELLER_SEND_GOODS' == $stdTrade['status'] && 'FRONT_PAID_FINAL_PAID' == $stdTrade['step_trade_status']) {
                    // 预售支付尾款
                    $queue[] = $this->formatQueue($stdTrade['tid'], HubRequestEnum::STEP_TRADE_CREATE);
                    // $queue[] = $this->formatQueue($stdTrade['tid'], HubRequestEnum::STEP_TRADE_PAID);
                }
            }
        }
        if ($queue) {
            $this->pushQueue($queue);
            // 校验下订单是否有取消，有的话需要推送取消。
            $tids = array_column($queue, 'bis_id');
            dispatch(new CancelTradeAfterCreatePushJob($tids));
        }
    }
}
