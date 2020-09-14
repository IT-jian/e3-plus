<?php


namespace App\Services\Adaptor\Taobao\Listeners;


use App\Services\Adaptor\Taobao\Events\TaobaoExchangeUpdateEvent;
use App\Services\Hub\HubRequestEnum;

/**
 * Class TaobaoExchangeUpdateListener
 * @package App\Services\Adaptor\Taobao\Listener
 *
 * @author linqihai
 * @since 2020/1/12 13:44
 */
class TaobaoExchangeUpdateListener
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
     * @param  TaobaoExchangeUpdateEvent  $event
     * @return void
     */
    public function handle(TaobaoExchangeUpdateEvent $event)
    {
        $queue = [];
        $stdExchange = $event->stdExchange;
        $existExchange = $event->existExchange;
        // ['买家已退货，待收货', 'WAIT_SELLER_CONFIRM_GOODS']
        if (!empty($stdExchange) && in_array($stdExchange['status'], ['待买家退货', '买家已退货，待收货', 'WAIT_BUYER_RETURN_GOODS', 'WAIT_SELLER_CONFIRM_GOODS'])) {
            if ($stdExchange['status'] != $existExchange['status'] && !in_array($existExchange['status'], ['待买家退货', 'WAIT_BUYER_RETURN_GOODS'])) {
                if (cutoverTrade($stdExchange['tid'], $stdExchange['platform'])) {
                    $queue[] = $this->formatQueue($stdExchange['dispute_id'], HubRequestEnum::EXCHANGE_CREATE_EXTEND);
                } else {
                    $queue[] = $this->formatQueue($stdExchange['dispute_id'], HubRequestEnum::EXCHANGE_CREATE);
                }
            }
        }
        if (!empty($stdExchange) && $stdExchange['status'] != $existExchange['status'] && in_array($stdExchange['status'], ['换货关闭', 'CLOSED', 'EXCHANGE_TRANSFER_REFUND', '请退款'])) { // 取消
            // 未审核取消的不推送
            $queue[] = $this->formatQueue($stdExchange['dispute_id'], HubRequestEnum::EXCHANGE_CANCEL);
        }
        if ($stdExchange['buyer_logistic_no'] != $existExchange['buyer_logistic_no']) {
            $queue[] = $this->formatQueue($stdExchange['dispute_id'], HubRequestEnum::EXCHANGE_RETURN_LOGISTIC_MODIFY);
        }
        if ($queue) {
            $this->pushQueue($queue);
        }
    }
}
