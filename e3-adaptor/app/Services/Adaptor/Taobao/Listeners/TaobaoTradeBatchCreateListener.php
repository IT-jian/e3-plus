<?php


namespace App\Services\Adaptor\Taobao\Listeners;

use App\Services\Adaptor\Jobs\TradeCreatePushJob;
use App\Services\Adaptor\Taobao\Events\TaobaoTradeBatchCreateEvent;

/**
 * Class TaobaoTradeBatchCreateListener
 * @package App\Services\Adaptor\Taobao\Listener
 *
 * @author linqihai
 * @since 2020/1/12 13:44
 */
class TaobaoTradeBatchCreateListener
{
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
     * @param TaobaoTradeBatchCreateEvent $event
     * @return void
     */
    public function handle(TaobaoTradeBatchCreateEvent $event)
    {
        $stdTrades = $event->stdTrades;
        foreach ($stdTrades as $stdTrade) {
            if (!empty($stdTrade) && in_array($stdTrade['status'], ['WAIT_SELLER_SEND_GOODS'])) {
                dispatch(new TradeCreatePushJob($stdTrade));
            }
        }
    }
}