<?php


namespace App\Services\Adaptor\Jingdong\Events;

/**
 * Class JingdongTradeBatchCreateEvent
 * @package App\Services\Adaptor\Jingdong\Events
 */
class JingdongTradeBatchCreateEvent
{
    public $stdTrades;

    public function __construct($stdTrades)
    {
        $this->stdTrades = $stdTrades;
    }
}