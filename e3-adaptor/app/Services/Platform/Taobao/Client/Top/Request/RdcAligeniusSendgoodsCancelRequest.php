<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 取消发货 - ag
 *
 * Class ExchangeGetRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setParam($value))
 *
 * @author linqihai
 * @since 2020/05/29 15:05
 */
class RdcAligeniusSendgoodsCancelRequest extends TopRequest
{

    protected $apiName = 'taobao.rdc.aligenius.sendgoods.cancel';

    protected $paramKeys    = [
        'param',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->param, "param");
    }
}