<?php


namespace App\Services\Hub\Adidas\JingdongRequest;


use App\Services\Hub\Adidas\Request\RefundReturnCreateRequest as BaseRequest;
use App\Services\Hub\Adidas\JingdongRequest\Transformer\RefundReturnCreateTransformer;

/**
 * 退单创建
 *
 * Class RefundReturnCreateRequest
 * @package App\Services\Hub\Adidas\JingdongRequest
 */
class RefundReturnCreateRequest extends BaseRequest
{
    /**
     *
     * @return RefundReturnCreateTransformer
     */
    public function getTransformer()
    {
        return app()->make(RefundReturnCreateTransformer::class);
    }
}