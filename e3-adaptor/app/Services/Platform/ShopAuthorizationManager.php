<?php


namespace App\Services\Platform;


use App\Services\Platform\Taobao\Shop\Authorization;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class ShopAuthorizationManager
{

    protected $platforms = [];

    /**
     * @param null $name
     * @return \App\Services\Platform\Contracts\Shop\AuthorizationContracts
     *
     * @author linqihai
     * @since 2019/12/13 15:44
     */
    public function platform($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->platforms[$name] = $this->get($name);
    }

    protected function get($name)
    {
        return $this->platforms[$name] ?? $this->resolve($name);
    }

    protected function getDefaultDriver()
    {
        return 'taobao';
    }

    protected function resolve($name)
    {
        $config = [];
        $driverMethod = 'create'.ucfirst($name).'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        } else {
            throw new InvalidArgumentException("Driver [{$name}] is not supported.");
        }
    }

    protected function createTaobaoDriver($config = [])
    {
        return new \App\Services\Platform\Taobao\Shop\Authorization();
    }

    protected function createJingdongDriver($config = [])
    {
        return new \App\Services\Platform\Jingdong\Shop\Authorization();
    }
}