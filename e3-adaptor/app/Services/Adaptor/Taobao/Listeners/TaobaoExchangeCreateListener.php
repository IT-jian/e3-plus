<?php


namespace App\Services\Adaptor\Taobao\Listeners;


use App\Services\Adaptor\Taobao\Events\TaobaoExchangeCreateEvent;
use App\Services\Hub\HubRequestEnum;

/**
 * Class TaobaoExchangeCreateListener
 * @package App\Services\Adaptor\Taobao\Listener
 * 插入待推送队列，仅支持批量插入
 *
 * @author linqihai
 * @since 2020/1/12 13:44
 */
class TaobaoExchangeCreateListener
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
     * @param  TaobaoExchangeCreateEvent  $event
     * @return void
     */
    public function handle(TaobaoExchangeCreateEvent $event)
    {
        $queue = [];
        $stdExchanges = $event->stdExchanges;
        foreach ($stdExchanges as $stdExchange) {
            if (!empty($stdExchange) && in_array($stdExchange['status'], ['待买家退货', '买家已退货，待收货', 'WAIT_BUYER_RETURN_GOODS', 'WAIT_SELLER_CONFIRM_GOODS'])) {
                // 是否有 cutover 加强版报文
                if (cutoverTrade($stdExchange['tid'], $stdExchange['platform'])) {
                    $queue[] = $this->formatQueue($stdExchange['dispute_id'], HubRequestEnum::EXCHANGE_CREATE_EXTEND);
                } else {
                    $queue[] = $this->formatQueue($stdExchange['dispute_id'], HubRequestEnum::EXCHANGE_CREATE);
                }
                if (!empty($stdExchange['buyer_logistic_no'])) {
                    $queue[] = $this->formatQueue($stdExchange['dispute_id'], HubRequestEnum::EXCHANGE_RETURN_LOGISTIC_MODIFY);
                }
            }
            if ($queue) {
                $this->pushQueue($queue);
            }
        }
    }
}
