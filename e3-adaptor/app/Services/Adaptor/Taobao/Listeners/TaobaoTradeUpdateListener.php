<?php


namespace App\Services\Adaptor\Taobao\Listeners;

use App\Services\Adaptor\Taobao\Events\TaobaoTradeUpdateEvent;
use App\Services\Hub\HubRequestEnum;

/**
 * Class TaobaoTradeCreateListener
 * @package App\Services\Adaptor\Taobao\Listener
 *
 * @author linqihai
 * @since 2020/1/12 13:44
 */
class TaobaoTradeUpdateListener
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
     * @param  TaobaoTradeUpdateEvent  $event
     * @return void
     */
    public function handle(TaobaoTradeUpdateEvent $event)
    {
        $queue = [];
        $stdTrade = $event->stdTrade;
        $existTrade = $event->existTrade;
        // 状态变更了，需要判断是否需要推送
        if (!empty($stdTrade) && $stdTrade['status'] != $existTrade['status'] && in_array($stdTrade['status'], ['WAIT_SELLER_SEND_GOODS']) && 'step' != $stdTrade['type']) {
            // dispatch(new TradeCreatePushJob($stdTrade)); // 推送job
            if (cutoverTrade($stdTrade['tid'], $stdTrade['platform'])) {
                $queue[] = $this->formatQueue($stdTrade['tid'], HubRequestEnum::TRADE_CREATE, 1, ['cutover' => '1']);
            } else {
                $queue[] = $this->formatQueue($stdTrade['tid'], HubRequestEnum::TRADE_CREATE);
            }
        } else if ('step' == $stdTrade['type']) {
            // 预售订单支付尾款, 订单状态变更，并且预售订单已支付尾款
            if ($this->shouldPushStepFrontPaid($stdTrade, $existTrade)) {
                $formatQueue = $this->formatForceQueue($stdTrade['tid'], HubRequestEnum::STEP_TRADE_CREATE, 0);
                $queue[] = $formatQueue;
                // $this->popIfExist($formatQueue);// 如果未同步则直接移除
            } else if ($this->shouldPushStepFinalPaid($stdTrade, $existTrade)) {
                $formatQueue = $this->formatForceQueue($stdTrade['tid'], HubRequestEnum::STEP_TRADE_PAID, 0);
                $queue[] = $formatQueue;
                // $this->popIfExist($formatQueue);// 如果未同步则直接移除
            } else if ($this->shouldPushStepCancel($stdTrade, $existTrade)) { // 未支付尾款，则触发取消
                $formatQueue = $this->formatForceQueue($stdTrade['tid'], HubRequestEnum::STEP_TRADE_CANCEL, 0);
                $queue[] = $formatQueue;
                // $this->popIfExist($formatQueue);
            }
        }

        if ($queue) {
            $this->pushQueue($queue);
        }
    }

    public function hasModifyAddress($stdTrade, $existTrade)
    {
        $addressFields = [
            'receiver_name',
            'receiver_country',
            'receiver_state',
            'receiver_city',
            'receiver_district',
            'receiver_town',
            'receiver_address',
            'receiver_zip',
            'receiver_mobile',
            'receiver_phone',
        ];
        $hasModified = false;
        foreach ($addressFields as $field) {
            if (isset($stdTrade[$field]) && $stdTrade[$field] != $existTrade[$field]) {
                $hasModified = true;
                break;
            }
        }
        if ($hasModified) {
            // 地址变更推送
            // dispatch(new TradeAddressModifyPushJob($stdTrade));
        }
    }

    /**
     * 预售订单是否支付定金
     * @param $stdTrade
     * @param $existTrade
     * @return bool
     *
     * @author linqihai
     * @since 2020/3/23 15:30
     */
    private function shouldPushStepFrontPaid($stdTrade, $existTrade)
    {
        return !empty($stdTrade)
            && ($stdTrade['status'] != $existTrade['status'] || $stdTrade['step_trade_status'] != $existTrade['step_trade_status'])
            && in_array($stdTrade['status'], ['WAIT_BUYER_PAY'])
            && 'step' == $stdTrade['type'] && 'FRONT_PAID_FINAL_NOPAID' == $stdTrade['step_trade_status'];
    }

    /**
     * 预售订单是否完成尾款支付
     * @param $stdTrade
     * @param $existTrade
     * @return bool
     *
     * @author linqihai
     * @since 2020/3/23 15:30
     */
    private function shouldPushStepFinalPaid($stdTrade, $existTrade)
    {
        return !empty($stdTrade)
            && ($stdTrade['status'] != $existTrade['status'] || $stdTrade['step_trade_status'] != $existTrade['step_trade_status'])
            && in_array($stdTrade['status'], ['WAIT_SELLER_SEND_GOODS'])
            && 'step' == $stdTrade['type'] && 'FRONT_PAID_FINAL_PAID' == $stdTrade['step_trade_status'];
    }

    /**
     * 预售订单未支付尾款，关闭订单
     *
     * @param $stdTrade
     * @param $existTrade
     * @return bool
     *
     * @author linqihai
     * @since 2020/3/23 15:32
     */
    private function shouldPushStepCancel($stdTrade, $existTrade)
    {
        return !empty($stdTrade) && 'step' == $stdTrade['type']
            && ($stdTrade['status'] != $existTrade['status'] || $stdTrade['step_trade_status'] != $existTrade['step_trade_status'])
            && in_array($stdTrade['status'], ['TRADE_CLOSED_BY_TAOBAO'])
            && 'FRONT_PAID_FINAL_NOPAID' == $stdTrade['step_trade_status'];
    }
}
