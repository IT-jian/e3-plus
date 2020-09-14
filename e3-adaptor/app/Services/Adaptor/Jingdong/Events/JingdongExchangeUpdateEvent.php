<?php


namespace App\Services\Adaptor\Jingdong\Events;

/**
 * Class JingdongExchangeUpdateEvent
 * 
 * @package App\Services\Adaptor\Jindong\Events
 */
class JingdongExchangeUpdateEvent
{
    public $stdExchange;
    public $existExchange;

    public function __construct($stdExchange, $existExchange)
    {
        $this->stdExchange = $stdExchange;
        $this->existExchange = $existExchange;
    }
}