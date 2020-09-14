<?php


namespace App\Services\Adaptor\Jingdong\Api;


use App\Facades\JosClient;
use App\Services\Platform\Exceptions\PlatformServerSideException;

class BaseApi
{
    protected $shop;

    public function request($request)
    {
        try {
            $response = JosClient::shop($this->shop['code'])->execute($request, $this->shop['access_token']);
        } catch (\Exception $e) {
            if ($e instanceof PlatformServerSideException) {
                if (33 == $e->getCode()) { // 翻页异常的，返回成功的空数据
                    return [];
                }
            }
            throw $e;
        }

        return $response;
    }
}