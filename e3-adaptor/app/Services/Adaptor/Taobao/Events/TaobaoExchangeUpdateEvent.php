<?php


namespace App\Services\Adaptor\Taobao\Events;

/**
 * 换货单更新
 *
 * Class TaobaoExchangeUpdateEvent
 * @package App\Services\Adaptor\Taobao\Events
 *
 * @author linqihai
 * @since 2020/1/12 13:44
 */
class TaobaoExchangeUpdateEvent
{
    public $stdExchange;
    public $existExchange;

    public function __construct($stdExchange, $existExchange)
    {
        $this->stdExchange = $stdExchange;
        $this->existExchange= $existExchange;
    }
}