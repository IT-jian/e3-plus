<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 店铺 token 刷新
 * Class TopAuthTokenRefreshRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setRefreshToken($value)
 *
 * @author linqihai
 * @since 2019/12/31 17:49
 */
class TopAuthTokenRefreshRequest extends TopRequest
{
    // https
    public $requireHttps = true;

    protected $apiName = 'taobao.top.auth.token.refresh';

    protected $paramKeys    = [
        'refreshToken',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
    }
}