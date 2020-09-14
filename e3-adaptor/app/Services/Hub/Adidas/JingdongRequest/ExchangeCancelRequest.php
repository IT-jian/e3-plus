<?php


namespace App\Services\Hub\Adidas\JingdongRequest;


use App\Services\Hub\Adidas\Request\ExchangeCancelRequest as BaseRequest;
use App\Services\Hub\Adidas\JingdongRequest\Transformer\ExchangeCancelTransformer;

/**
 * 换货单取消
 *
 * Class ExchangeCancelRequest
 * @package App\Services\Hub\Adidas\JingdongRequest
 */
class ExchangeCancelRequest extends BaseRequest
{
    /**
     *
     * @return ExchangeCancelTransformer
     */
    public function getTransformer()
    {
        return app()->make(ExchangeCancelTransformer::class);
    }
}