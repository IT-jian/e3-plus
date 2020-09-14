<?php


namespace App\Services\Adaptor;


class AdaptorManager
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    protected $adaptors = array();

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

    public function getDefaultAdaptor()
    {
        return $this->app['config']['adaptor.default'];
    }

    public function getConfig($name)
    {
        return $this->app['config']["adaptor.adaptors.{$name}"];
    }

    /**
     * 实例化指定平台服务
     *
     * @param $name
     * @return mixed
     *
     * @author linqihai
     * @since 2019/12/25 9:56
     */
    public function platform($name)
    {
        return $this->adaptor($name);
    }

    public function makeAdaptor($name)
    {
        return $this->resolve($name);
    }

    public function adaptor($name)
    {
        if ( ! isset($this->adaptors[$name]))
        {
            $this->adaptors[$name] = $this->makeAdaptor($name);
        }
        return $this->adaptors[$name];
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
        $name = $name ?? $this->getDefaultAdaptor();
        $config = $this->getConfig($name);
        $driverMethod = 'create'.ucfirst($name).'Adaptor';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        } else {
            throw new \InvalidArgumentException("Adaptor [{$name}] is not supported.");
        }
    }

    protected function createTaobaoAdaptor($config = [])
    {
        return new TaobaoAdaptor();
    }

    protected function createJingdongAdaptor($config = [])
    {
        return new JingdongAdaptor();
    }
}