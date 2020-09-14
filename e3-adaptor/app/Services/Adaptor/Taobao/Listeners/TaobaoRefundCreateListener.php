<?php


namespace App\Services\Adaptor\Taobao\Listeners;

use App\Services\Adaptor\Taobao\Events\TaobaoRefundCreateEvent;
use App\Services\Wms\Jobs\AdidasWmsPushJob;

/**
 * 退单新增处理
 *
 * Class TaobaoRefundCreateListener
 * @package App\Services\Adaptor\Taobao\Listener
 *
 * @author linqihai
 * @since 2020/1/12 13:44
 */
class TaobaoRefundCreateListener
{
    use TaobaoRefundListenerTrait;

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
     * @param TaobaoRefundCreateEvent $event
     * @return void
     */
    public function handle(TaobaoRefundCreateEvent $event)
    {
        $this->queue = [];
        $stdRefunds = $event->stdRefunds;
        $refundIds = [];
        foreach ($stdRefunds as $stdRefund) {
            // 订单取消
            $this->handleTradeCancel($stdRefund);
            // 退单创建
            $this->handleRefundCreate($stdRefund);
            // 退单取消
            // $this->handleRefundCancel($stdRefund);
            // gwc 创建
            $this->handleGwcCreate($stdRefund);
            //gwc 取消
            $this->handleGwcCancel($stdRefund);

            // 推送wms任务
            if (in_array($stdRefund['status'], ['SUCCESS', 'WAIT_SELLER_AGREE'])
                && in_array($stdRefund['order_status'], ['WAIT_SELLER_SEND_GOODS', 'ALL_CLOSED', 'TRADE_CLOSED', 'TRADE_CLOSED_BY_TAOBAO'])) {
                $refundIds[] = $stdRefund['refund_id'];
            }
        }

        // 推送wms任务
        if ($refundIds) {
            dispatch(new AdidasWmsPushJob($refundIds));
        }
        if (!empty($stdRefunds)) {
            $this->pushQueue($this->queue);
            unset($this->queue);
        }
    }
}
