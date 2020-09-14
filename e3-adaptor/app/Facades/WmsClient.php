<?php


namespace App\Facades;

use App\Services\Wms\Contracts\WmsClientContract;
use App\Services\Wms\WmsClientManager;
use Illuminate\Support\Facades\Facade;

/**
 *
 * @method static WmsClientContract|WmsClientManager wms(string $name = null)
 * @method array execute($requests) 执行
 * @method \App\Services\Wms\Contracts\RequestContract resolveRequestClass($method) 解析类
 * 退单取消成功
 * @method array tradeCancelSuccess($refund) 退单取消成功
 *
 * @method array getBody() 获取请求报文
 * @method array get() 获取请求报文
 *
 */
class WmsClient  extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'wmsclient';
    }
}
