<?php


namespace App\Services\Adaptor\Taobao\Events;

/**
 * 退单新增
 *
 * Class TaobaoRefundCreateEvent
 * @package App\Services\Adaptor\Taobao\Events
 *
 * @author linqihai
 * @since 2020/1/12 13:44
 */
class TaobaoRefundCreateEvent
{
    public $stdRefunds;

    public function __construct($stdRefunds)
    {
        $this->stdRefunds = $stdRefunds;
    }
}