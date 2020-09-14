<?php


namespace App\Services\HubApi\Adidas;

use App\Services\HubApi\Adidas\Jingdong\JingdongApi;
use App\Services\HubApi\Adidas\Taobao\TaobaoApi;

/**
 * 校验请求
 * 格式化请求
 * 转发平台
 * 响应
 *
 * Class AdidasApi
 * @package App\Services\HubApi\Adidas
 *
 * @author linqihai
 * @since 2019/12/30 14:09
 */
class AdidasApiManager
{
    protected $platforms = [];

    /**
     * @param null $name
     * @return \App\Services\HubApi\Contracts\HubPlatformApiContract
     *
     * @author linqihai
     * @since 2019/12/31 18:18
     */
    public function platform($name = null)
    {
        if (!isset($this->platforms[$name])) {
            $this->platforms[$name] = $this->makeHubApi($name);
        }

        return $this->platforms[$name];
    }

    public function makeHubApi($name)
    {
        return $this->resolve($name);
    }

    /**
     * @param $name
     * @return mixed
     *
     * @author linqihai
     * @since 2019/12/25 9:57
     */
    protected function resolve($name)
    {
        $config = [];
        $driverMethod = 'create' . ucfirst($name) . 'Api';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        } else {
            throw new \InvalidArgumentException("Platform Api [{$name}] is not supported.");
        }
    }

    protected function createTaobaoApi($config = [])
    {
        return new TaobaoApi();
    }

    protected function createJingdongApi($config = [])
    {
        return new JingdongApi();
    }
}