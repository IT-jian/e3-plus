<?php


namespace App\Services\Adaptor\Jingdong\Events;

/**
 * Class JingdongStepTradeCreateEvent
 * @package App\Services\Adaptor\Jindong\Events
 */
class JingdongStepTradeCreateEvent
{
    public $stdTrades;

    public function __construct($stdTrades)
    {
        $this->stdTrades = $stdTrades;
    }
}