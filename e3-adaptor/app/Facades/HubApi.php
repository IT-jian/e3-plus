<?php


namespace App\Facades;

use App\Services\Hub\Contracts\HubClientContract;
use App\Services\HubApi\Contracts\HubApiContract;
use App\Services\HubApi\Contracts\HubPlatformApiContract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Services\HubApi\Contracts\HubApiContract hub(string $name = null)
 * @method static mixed check($request)
 * @method static \App\Services\HubApi\Contracts\HubPlatformApiContract platform($refund)
 * @method static mixed exchangeCreate($exchange)
 * @method static mixed skuSync($sku)
 *
 * @see \App\Services\HubApi\HubApiManager
 */
class HubApi  extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'hubapi';
    }
}