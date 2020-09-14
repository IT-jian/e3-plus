<?php


namespace App\Facades;


use Illuminate\Support\Facades\Facade;

/**
 * taobao open platform client
 *
 * @method static \App\Services\Platform\Taobao\Client\Top\TopClientNew shop($shop)
 * @method static mixed forget($shopCode)
 * @method static \App\Services\Platform\Taobao\Client\Top\TopClientNew topClient($refund)
 *
 * @see \App\Services\Platform\Taobao\Client\Top\TopClientManager
 */
class TopClient extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'topclient';
    }
}