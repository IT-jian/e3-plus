<?php


namespace App\Services\Adaptor\Jingdong\Events;

/**
 * Class JingdongTradeCreateEvent
 * @package App\Services\Adaptor\Jindong\Events
 */
class JingdongTradeCreateEvent
{
    public $stdTrades;

    public function __construct($stdTrades)
    {
        $this->stdTrades = $stdTrades;
    }
}