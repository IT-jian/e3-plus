<?php


namespace App\Services\Wms;


use App\Services\Wms\Contracts\WmsClientContract;
use App\Services\Wms\Shunfeng\Adidas\ShunfengAdidasClient;
use Illuminate\Support\Str;

class ShunfengWmsClient implements WmsClientContract
{

    protected static $methodClassMap = [];

    /**
     * @var ShunfengAdidasClient
     */
    protected $client;

    public function __construct(ShunfengAdidasClient $client)
    {
        $this->client = $client;
    }

    /**
     * 自动解析 request 实例
     *
     * @param $method
     * @param $parameters
     * @return array
     * @throws \Exception
     *
     * @author linqihai
     * @since 2020/1/6 14:36
     */
    public function __call($method, $parameters)
    {
        $request = $this->resolveRequestClass($method);
        // 设置请求内容
        $request = $request->setContent(...$parameters);

        $result = $this->execute([$request]);

        return current($result);
    }

    /**
     * @param $method
     * @return \App\Services\Hub\Adidas\Request\RequestContract
     * @throws \Exception
     *
     * @author linqihai
     * @since 2020/1/6 14:29
     */
    public function resolveRequestClass($method)
    {
        $className = '\App\Services\Wms\Shunfeng\Adidas\Request\\';
        $className .= ucfirst($method).'Request';
        if (!class_exists($className)) {
            throw new \BadMethodCallException('No Such Wms Shunfeng Adidas API ' . $method);
        }

        return app()->make($className);
    }

    public function execute($requests)
    {
        return $this->client->execute($requests);
    }
}
