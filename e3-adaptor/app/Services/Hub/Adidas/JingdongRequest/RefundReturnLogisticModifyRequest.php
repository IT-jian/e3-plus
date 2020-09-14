<?php


namespace App\Services\Hub\Adidas\JingdongRequest;


use App\Services\Hub\Adidas\Request\RefundReturnLogisticModifyRequest as BaseRequest;
use App\Services\Hub\Adidas\JingdongRequest\Transformer\RefundReturnLogisticModifyTransformer;

/**
 * 退单快递信息更新
 *
 * Class RefundReturnLogisticModifyRequest
 * @package App\Services\Hub\Adidas\JingdongRequest
 */
class RefundReturnLogisticModifyRequest extends BaseRequest
{
    /**
     *
     * @return RefundReturnLogisticModifyTransformer
     */
    public function getTransformer()
    {
        return app()->make(RefundReturnLogisticModifyTransformer::class);
    }
}