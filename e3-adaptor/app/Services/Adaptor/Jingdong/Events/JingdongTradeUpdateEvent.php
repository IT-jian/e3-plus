<?php


namespace App\Services\Adaptor\Jingdong\Events;

/**
 * Class JingdongTradeUpdateEvent
 * @package App\Services\Adaptor\Jindong\Events
 */
class JingdongTradeUpdateEvent
{
    public $stdTrade;
    public $existTrade;

    public function __construct($stdTrade, $existTrade)
    {
        $this->stdTrade = $stdTrade;
        $this->existTrade = $existTrade;
    }
}