<?php


namespace App\Services\Adaptor;


abstract class BaseAdaptor
{
    abstract public function platformType();


    /**
     * 下载
     *
     * @param string $type 业务类型
     * @param array $params 参数
     *
     * @return mixed
     *
     * @author linqihai
     * @since 2020/1/10 10:57
     */
    public function download($type, $params)
    {
        $downloaderClass = $this->resolveDownloader($type);

        return $downloaderClass->download($params);
    }

    /**
     * 转为标准单
     *
     * @param string $type 业务类型
     * @param $params
     * @return mixed
     *
     * @author linqihai
     * @since 2020/1/10 10:58
     */
    public function transfer($type, $params)
    {
        $downloaderClass = $this->resolveTransformer($type);

        return $downloaderClass->transfer($params);
    }

    /**
     * 解析下载类
     *
     * @param $method
     * @return mixed
     *
     * @author linqihai
     * @since 2020/1/10 10:58
     */
    public function resolveDownloader($method)
    {
        $platform = $this->platformType();

        $className = "App\Services\Adaptor\\";
        $className .= ucfirst($platform) ."\Downloader\\";
        $className .= ucfirst($method) . 'Downloader';
        if (!class_exists($className)) {
            throw new \BadMethodCallException('No Such Adaptor Downloader');
        }

        return app()->make($className);
    }

    /**
     * 解析转入类
     *
     * @param $method
     * @return mixed
     *
     * @author linqihai
     * @since 2020/1/10 10:59
     */
    public function resolveTransformer($method)
    {
        $platform = $this->platformType();

        $className = "App\Services\Adaptor\\";
        $className .= ucfirst($platform) ."\Transformer\\";
        $className .= ucfirst($method) . 'Transformer';
        if (!class_exists($className)) {
            throw new \BadMethodCallException('No Such Adaptor Transformer');
        }

        return app()->make($className);
    }
}