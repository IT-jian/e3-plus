<?php


namespace App\Services\Adaptor\Taobao\Events;

/**
 * 换货单新增事件
 *
 * Class TaobaoExchangeCreateEvent
 * @package App\Services\Adaptor\Taobao\Events
 *
 * @author linqihai
 * @since 2020/1/12 13:44
 */
class TaobaoExchangeCreateEvent
{
    public $stdExchanges;

    public function __construct($stdExchanges)
    {
        $this->stdExchanges = $stdExchanges;
    }
}