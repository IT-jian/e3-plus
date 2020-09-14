<?php


namespace App\Services\Hub;


class HufuHubClient extends AdidasHubClient
{
    /**
     * @param $method
     * @return \App\Services\Hub\Hufu\Request\RequestContract
     * @throws \Exception
     */
    public function resolveRequestClass($method)
    {
        $className = '\App\Services\Hub\Hufu\Adidas\Request\\';
        $className .= ucfirst($method).'Request';
        if (!class_exists($className)) {
            throw new \BadMethodCallException('No Such Hub API ' . $method);
        }

        return app()->make($className);
    }
}
