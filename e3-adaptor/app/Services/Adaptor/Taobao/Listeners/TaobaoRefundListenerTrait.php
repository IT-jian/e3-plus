<?php


namespace App\Services\Adaptor\Taobao\Listeners;

use App\Services\Hub\HubRequestEnum;

trait TaobaoRefundListenerTrait
{
    use TaobaoPushQueueTrait;

    protected $queue;

    /**
     * 取消订单
     *
     * @param $stdRefund
     *
     * @author linqihai
     * @since 2020/1/13 10:57
     */
    public function handleTradeCancel($stdRefund)
    {
        if (in_array($stdRefund['status'], ['WAIT_SELLER_AGREE', 'SUCCESS'])
            && in_array($stdRefund['order_status'], ['WAIT_SELLER_SEND_GOODS', 'ALL_CLOSED', 'TRADE_CLOSED', 'TRADE_CLOSED_BY_TAOBAO'])) {
            // dispatch(new TradeCancelPushJob($stdRefund));
            if (!cutoverTrade($stdRefund['tid'], $stdRefund['platform'])) {
                $this->queue[] = $this->formatQueue($stdRefund['refund_id'], HubRequestEnum::TRADE_CANCEL);
            }
        }
    }

    /**
     * 退单创建 Return Order Creation
     *
     * @param $stdRefund
     *
     * @author linqihai
     * @since 2020/1/13 11:00
     */
    public function handleRefundCreate($stdRefund)
    {
        if (1 == $stdRefund['has_good_return']) {
            if (in_array($stdRefund['status'], ['WAIT_BUYER_RETURN_GOODS', 'WAIT_SELLER_CONFIRM_GOODS'])) {
                // dispatch(new RefundReturnCreatePushJob($stdRefund));
                // 是否有 cutover 加强版报文
                if (cutoverTrade($stdRefund['tid'], $stdRefund['platform'])) {
                    $this->queue[] = $this->formatQueue($stdRefund['refund_id'], HubRequestEnum::REFUND_RETURN_CREATE_EXTEND);
                } else {
                    $this->queue[] = $this->formatQueue($stdRefund['refund_id'], HubRequestEnum::REFUND_RETURN_CREATE);
                }
                if (!empty($stdRefund['sid'])) {
                    $this->queue[] = $this->formatQueue($stdRefund['refund_id'], HubRequestEnum::REFUND_RETURN_LOGISTIC_MODIFY);
                }
            }
        }
    }

    /**
     * 退单取消 Return Order Cancellation
     * 订单发货之后，申请退货退款，消费者关闭或者超时自动关闭的退款请求
     *
     * @param $stdRefund
     *
     * @author linqihai
     * @since 2020/1/13 11:00
     */
    public function handleRefundCancel($stdRefund)
    {
        if (1 == $stdRefund['has_good_return']) {
            if (in_array($stdRefund['status'], ['CLOSED'])) {
                $this->queue[] = $this->formatQueue($stdRefund['refund_id'], HubRequestEnum::REFUND_RETURN_CANCEL);
            }
        }
    }

    /**
     * 订单已经发货，消费者申请退运费等部分只退款不退货的退款请求，发货之后仅退款
     *
     * @param $stdRefund
     *
     * @author linqihai
     * @since 2020/1/13 11:10
     */
    public function handleGwcCreate($stdRefund)
    {
        return true;
        if (0 == $stdRefund['has_good_return']) {
            if (in_array($stdRefund['status'], ['SUCCESS', 'WAIT_SELLER_AGREE'])
                && in_array($stdRefund['order_status'], ['WAIT_BUYER_CONFIRM_GOODS', 'TRADE_BUYER_SIGNED', 'TRADE_FINISHED'])) {
                // dispatch(new RefundCreatePushJob($stdRefund));
                $this->queue[] = $this->formatQueue($stdRefund['refund_id'], HubRequestEnum::REFUND_CREATE);
            }
        }
    }

    /**
     * 订单已经发货，消费者申请退运费等部分只退款不退货的退款请求，发货之后仅退款，取消申请
     *
     * @param $stdRefund
     *
     * @author linqihai
     * @since 2020/1/13 11:16
     */
    public function handleGwcCancel($stdRefund)
    {
        return true;
        if (0 == $stdRefund['has_good_return']) {
            if (in_array($stdRefund['status'], ['CLOSED'])
                && in_array($stdRefund['order_status'], ['WAIT_BUYER_CONFIRM_GOODS', 'TRADE_BUYER_SIGNED', 'TRADE_FINISHED'])) {
                // dispatch(new RefundCancelPushJob($stdRefund));
                $this->queue[] = $this->formatQueue($stdRefund['refund_id'], HubRequestEnum::REFUND_CANCEL);
            }
        }
    }
}
