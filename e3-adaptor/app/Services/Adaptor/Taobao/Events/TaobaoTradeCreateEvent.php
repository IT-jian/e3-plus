<?php


namespace App\Services\Adaptor\Taobao\Events;

/**
 * Class TaobaoTradeCreateEvent
 * @package App\Services\Adaptor\Taobao\Events
 *
 * @author linqihai
 * @since 2020/1/12 13:44
 */
class TaobaoTradeCreateEvent
{
    public $stdTrades;

    public function __construct($stdTrades)
    {
        $this->stdTrades = $stdTrades;
    }
}