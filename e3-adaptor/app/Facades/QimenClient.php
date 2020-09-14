<?php


namespace App\Facades;


use Illuminate\Support\Facades\Facade;

/**
 * taobao open platform client
 *
 * @method static \App\Services\Platform\Taobao\Qimen\Top\QimenCloudClient shop($shop)
 * @method static mixed forget($shopCode)
 * @method static \App\Services\Platform\Taobao\Qimen\Top\QimenCloudClient topClient($refund)
 *
 * @see \App\Services\Platform\Taobao\Qimen\Top\QimenClientManager
 */
class QimenClient extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'qimen';
    }
}
