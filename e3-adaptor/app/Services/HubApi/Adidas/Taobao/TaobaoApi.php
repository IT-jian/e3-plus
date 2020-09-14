<?php

namespace App\Services\HubApi\Adidas\Taobao;


use App\Services\HubApi\Contracts\HubPlatformApiContract;

class TaobaoApi implements HubPlatformApiContract
{
    /**
     * 调用接口，转发请求到平台
     *
     * @param $params
     * @return mixed
     * @throws \Exception
     *
     * @author linqihai
     * @since 2019/12/31 15:59
     */
    public function execute($params)
    {
        $class = $this->resolveClass($params['method']);
        $class->setData($params['content']);
        $class->check();
        // 执行代理
        $result = $class->proxy();

        return $result;
    }

    public function mock($params)
    {
        $class = $this->resolveClass($params['method']);
        $class->setData($params['content']);
        $class->check();
    }

    /**
     * 解析请求类
     * @param $method
     * @return \Laravel\Lumen\Application|mixed
     * @throws \Exception
     *
     * @author linqihai
     * @since 2019/12/31 16:00
     */
    private function resolveClass($method)
    {
        $className = '\App\Services\HubApi\Adidas\Taobao\Api\\';
        $className .= ucfirst($method).'HubApi';
        if (!class_exists($className)) {
            throw new \BadMethodCallException('No such API');
        }

        return app()->make($className);
    }
}