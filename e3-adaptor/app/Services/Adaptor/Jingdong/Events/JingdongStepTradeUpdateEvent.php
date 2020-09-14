<?php


namespace App\Services\Adaptor\Jingdong\Events;

/**
 * Class JingdongStepTradeUpdateEvent
 * @package App\Services\Adaptor\Jindong\Events
 */
class JingdongStepTradeUpdateEvent
{
    public $stdTrade;
    public $existTrade;

    public function __construct($stdTrade, $existTrade)
    {
        $this->stdTrade = $stdTrade;
        $this->existTrade = $existTrade;
    }
}