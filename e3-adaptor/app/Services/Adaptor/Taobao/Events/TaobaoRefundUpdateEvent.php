<?php


namespace App\Services\Adaptor\Taobao\Events;

/**
 * 退单更新
 *
 * Class TaobaoRefundCreateEvent
 * @package App\Services\Adaptor\Taobao\Events
 *
 * @author linqihai
 * @since 2020/1/12 13:44
 */
class TaobaoRefundUpdateEvent
{
    public $stdRefund;
    public $existRefund;

    public function __construct($stdRefund, $existRefund)
    {
        $this->stdRefund = $stdRefund;
        $this->existRefund = $existRefund;
    }
}