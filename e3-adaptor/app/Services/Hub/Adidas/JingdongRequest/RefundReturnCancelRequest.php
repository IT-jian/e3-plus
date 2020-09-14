<?php


namespace App\Services\Hub\Adidas\JingdongRequest;


use App\Services\Hub\Adidas\Request\RefundReturnCancelRequest as BaseRequest;
use App\Services\Hub\Adidas\JingdongRequest\Transformer\RefundReturnCancelTransformer;

/**
 * 退单取消
 *
 * Class RefundReturnLogisticModifyRequest
 * @package App\Services\Hub\Adidas\JingdongRequest
 */
class RefundReturnCancelRequest extends BaseRequest
{
    /**
     *
     * @return RefundReturnCancelTransformer
     */
    public function getTransformer()
    {
        return app()->make(RefundReturnCancelTransformer::class);
    }
}