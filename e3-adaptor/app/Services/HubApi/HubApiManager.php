<?php


namespace App\Services\HubApi;


use App\Services\Hub\Contracts\HubClientContract;
use App\Services\HubApi\Adidas\AdidasApiManager;

class HubApiManager
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    protected $hubs = array();

    /**
     * Create a new Cache manager instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    protected function getConfig($name)
    {
        return $this->app['config']["hubapi.clients.{$name}"];
    }

    /**
     * Get the default hub client name.
     *
     * @return string
     */
    public function getDefaultHub()
    {
        return $this->app['config']['hubapi.default'];
    }

    public function makeHub($name)
    {
        return $this->resolve($name);
    }

    /**
     * @param $name
     * @return HubClientContract
     *
     * @author linqihai
     * @since 2019/12/26 14:36
     */
    public function hub($name = null)
    {
        $name = $name ?? $this->getDefaultHub();
        if ( ! isset($this->hubs[$name]))
        {
            $this->hubs[$name] = $this->makeHub($name);
        }
        return $this->hubs[$name];
    }

    /**
     * 解析全部适配平台
     *
     * @param $name
     * @return mixed
     *
     * @author linqihai
     * @since 2019/12/25 9:57
     */
    protected function resolve($name)
    {
        $name = $name ?? $this->getDefaultHub();
        $config = $this->getConfig($name);
        $driverMethod = 'create'.ucfirst($name).'HubApi';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        } else {
            throw new \InvalidArgumentException("Hub [{$name}] is not supported.");
        }
    }

    protected function createAdidasHubApi($config = [])
    {
        return new AdidasHubApi(new AdidasApiManager());
    }

    /**
     * 直接调用 hub 方法
     *
     * @param $method
     * @param $parameters
     * @return mixed
     *
     * @author linqihai
     * @since 2019/12/26 14:40
     */
    public function __call($method, $parameters)
    {
        return $this->hub()->$method(...$parameters);
    }
}