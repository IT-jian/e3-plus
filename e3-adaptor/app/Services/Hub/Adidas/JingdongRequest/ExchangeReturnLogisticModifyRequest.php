<?php


namespace App\Services\Hub\Adidas\JingdongRequest;


use App\Services\Hub\Adidas\Request\ExchangeReturnLogisticModifyRequest as BaseRequest;
use App\Services\Hub\Adidas\JingdongRequest\Transformer\ExchangeReturnLogisticModifyTransformer;

/**
 * 换单物流更新接口
 *
 * Class RefundReturnLogisticModifyRequest
 * @package App\Services\Hub\Adidas\JingdongRequest
 */
class ExchangeReturnLogisticModifyRequest extends BaseRequest
{
    /**
     *
     * @return ExchangeReturnLogisticModifyTransformer
     */
    public function getTransformer()
    {
        return app()->make(ExchangeReturnLogisticModifyTransformer::class);
    }
}