<?php


namespace App\Services\Platform\Taobao\Qimen\Api;


use App\Services\Platform\Taobao\Qimen\ApiContracts;

/**
 * 千牛自助截单
 * Class OrderSelfInterceptApi
 * @package App\Services\Platform\Taobao\Qimen\Api
 *
 * @author linqihai
 * @since 2020/3/23 15:23
 */
class OrderSelfInterceptApi extends BaseQimenApi implements ApiContracts
{

    /**
     * 执行请求
     *
     * @param $request
     * @return mixed
     *
     * @author linqihai
     * @since 2020/3/23 11:45
     */
    public function execute($request)
    {
        $result = [
            'status'  => false,
            'message' => '不支持的接口请求',
        ];

        return $result;
    }
}