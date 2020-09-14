<?php


namespace App\Services\Hub\Adidas\JingdongRequest;


use App\Services\Hub\Adidas\Request\ExchangeCreateRequest as BaseRequest;
use App\Services\Hub\Adidas\JingdongRequest\Transformer\ExchangeCreateTransformer;

/**
 * 换货单创建
 *
 * Class ExchangeCreateRequest
 * @package App\Services\Hub\Adidas\JingdongRequest
 */
class ExchangeCreateRequest extends BaseRequest
{
    /**
     *
     * @return ExchangeCreateTransformer
     */
    public function getTransformer()
    {
        return app()->make(ExchangeCreateTransformer::class);
    }
}
