<?php


namespace App\Facades;


use Illuminate\Support\Facades\Facade;

/**
 * jingdong open platform client
 *
 * @method static \App\Services\Platform\Jingdong\Client\Jos\JosClient shop($shop)
 * @method static mixed forget($shopCode)
 * @method static \App\Services\Platform\Jingdong\Client\Jos\JosClient josClient($refund)
 *
 * @see \App\Services\Platform\Jingdong\Client\Jos\JosClientManager
 */
class JosClient extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'josclient';
    }
}