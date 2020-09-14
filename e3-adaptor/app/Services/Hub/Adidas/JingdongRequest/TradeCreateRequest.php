<?php


namespace App\Services\Hub\Adidas\JingdongRequest;


use App\Services\Hub\Adidas\Request\TradeCreateRequest as BaseRequest;
use App\Services\Hub\Adidas\JingdongRequest\Transformer\TradeCreateTransformer;

/**
 * 京东订单创建
 *
 * Class TradeCreateRequest
 * @package App\Services\Hub\Adidas\JingdongRequest
 */
class TradeCreateRequest extends BaseRequest
{

    /**
     * @todo 换成京东 transformer
     * @return TradeCreateTransformer
     */
    public function getTransformer()
    {
        return app()->make(TradeCreateTransformer::class);
    }

    /**
     * 请求完成后回调
     *
     * @param array $parsedData
     *
     * @return bool
     */
    public function responseCallback($parsedData)
    {
        return true;
    }
}
