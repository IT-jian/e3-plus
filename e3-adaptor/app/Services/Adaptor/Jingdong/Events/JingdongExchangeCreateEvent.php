<?php


namespace App\Services\Adaptor\Jingdong\Events;

/**
 * Class JingdongExchangeCreateEvent
 * @package App\Services\Adaptor\Jindong\Events
 */
class JingdongExchangeCreateEvent
{
    public $stdExchanges;

    public function __construct($stExchanges)
    {
        $this->stdExchanges = $stExchanges;
    }
}