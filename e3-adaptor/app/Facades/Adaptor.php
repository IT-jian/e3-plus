<?php


namespace App\Facades;


use App\Services\Adaptor\Contracts\AdaptorContract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static AdaptorContract platform(string $name)
 * @method static AdaptorContract adaptor(string $name)
 *
 * @see \App\Services\Adaptor\AdaptorManager
 */
class Adaptor extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'adaptor';
    }
}