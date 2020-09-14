<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 店铺token生成
 *
 * Class TopAuthTokenCreateRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setCode($value)
 *
 * @author linqihai
 * @since 2019/12/31 17:49
 */
class TopAuthTokenCreateRequest extends TopRequest
{
    // https
    public $requireHttps = true;

    protected $apiName = 'taobao.top.auth.token.create';

    protected $paramKeys    = [
        'code',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
    }
}