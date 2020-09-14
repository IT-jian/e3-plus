<?php


namespace App\Services\Adaptor\Jingdong\Events;

/**
 * Class JingdongRefundCreateEvent
 * @package App\Services\Adaptor\Jindong\Events
 */
class JingdongRefundCreateEvent
{
    public $stdRefunds;

    public function __construct($stdRefunds)
    {
        $this->stdRefunds = $stdRefunds;
    }
}