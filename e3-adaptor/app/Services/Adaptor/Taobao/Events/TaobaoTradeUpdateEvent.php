<?php


namespace App\Services\Adaptor\Taobao\Events;

/**
 * Class TaobaoTradeUpdateEvent
 * @package App\Services\Adaptor\Taobao\Events
 *
 * @author linqihai
 * @since 2020/1/12 13:44
 */
class TaobaoTradeUpdateEvent
{
    public $stdTrade;
    public $existTrade;

    public function __construct($stdTrade, $existTrade)
    {
        $this->stdTrade = $stdTrade;
        $this->existTrade = $existTrade;
    }
}