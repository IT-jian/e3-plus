<?php


namespace App\Services\Adaptor\Jingdong\Events;

/**
 * Class JingdongRefundUpdateEvent
 *
 * @package App\Services\Adaptor\Jindong\Events
 */
class JingdongRefundUpdateEvent
{
    public $stdRefund;
    public $existRefund;

    public function __construct($stdRefund, $existRefund)
    {
        $this->stdRefund = $stdRefund;
        $this->existRefund = $existRefund;
    }
}