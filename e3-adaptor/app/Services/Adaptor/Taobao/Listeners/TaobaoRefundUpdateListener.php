<?php


namespace App\Services\Adaptor\Taobao\Listeners;

use App\Services\Adaptor\Taobao\Events\TaobaoRefundUpdateEvent;
use App\Services\Hub\HubRequestEnum;
use App\Services\Wms\Jobs\AdidasWmsPushJob;

/**
 * 退单更新处理
 *
 * Class TaobaoRefundCreateListener
 * @package App\Services\Adaptor\Taobao\Listener
 *
 * @author linqihai
 * @since 2020/1/12 13:44
 */
class TaobaoRefundUpdateListener
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
     * @param  TaobaoRefundUpdateEvent  $event
     * @return void
     */
    public function handle(TaobaoRefundUpdateEvent $event)
    {
        $this->queue = [];
        $stdRefund = $event->stdRefund;
        $existRefund = $event->existRefund;
        // 更新退单号
        if (empty($existRefund['company_name']) || empty($existRefund['sid'])) {
            if ($stdRefund['company_name'] != $existRefund['company_name'] || $stdRefund['sid'] != $existRefund['sid']) {
                // dispatch(new RefundReturnLogisticModifyPushJob($stdRefund));
                $this->queue[] = $this->formatQueue($stdRefund['refund_id'], HubRequestEnum::REFUND_RETURN_LOGISTIC_MODIFY);
            }
        }

        // 状态变更，则重新处理
        if ($stdRefund['status'] != $existRefund['status'] || $stdRefund['order_status'] != $existRefund['order_status']) {
            // 订单取消
            $this->handleTradeCancel($stdRefund);
            // 退单创建
            if ($stdRefund['status'] != $existRefund['status'] && !in_array($existRefund['status'],['WAIT_BUYER_RETURN_GOODS'])) {// 已推送过的不再推送
                $this->handleRefundCreate($stdRefund);
            }
            // 退单取消，未同意的退货单取消，不推送请求
            if (!in_array($existRefund['status'], ['WAIT_SELLER_AGREE'])) {
                $this->handleRefundCancel($stdRefund);
            }
            // gwc 创建
            $this->handleGwcCreate($stdRefund);
            //gwc 取消
            $this->handleGwcCancel($stdRefund);
        }

        // 推送wms任务
        if (in_array($stdRefund['status'], ['SUCCESS', 'WAIT_SELLER_AGREE']) && ($stdRefund['status'] != $existRefund['status'] || $stdRefund['order_status'] != $existRefund['order_status'])
            && in_array($stdRefund['order_status'], ['WAIT_SELLER_SEND_GOODS', 'ALL_CLOSED', 'TRADE_CLOSED', 'TRADE_CLOSED_BY_TAOBAO'])) {
            dispatch(new AdidasWmsPushJob([$stdRefund['refund_id']]));
        }
        if ($this->queue) {
            $this->pushQueue($this->queue);
        }
    }
}
