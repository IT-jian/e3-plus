<?php


namespace App\Services\Adaptor\Jingdong\Api;


use App\Services\Platform\Jingdong\Client\Jos\JosClient;
use App\Services\Platform\Jingdong\Client\Jos\Request\RefundApplyQueryPageListRequest;

/**
 * 交易号查询退款信息
 *
 * Class RefundApplyByOrderId
 * @package App\Services\Adaptor\Jingdong\Api
 */
class RefundApplyByOrderId
{

    private $shop;

    public function __construct($shop)
    {
        $this->shop = $shop;
    }

    /**
     * @param $orderId
     * @return mixed
     */
    public function refundApply($orderId)
    {
        $request = new RefundApplyQueryPageListRequest();
        $request->setOrderId($orderId);

        $list = JosClient::shop($this->shop['code'])->execute($request, $this->shop['access_token']);

        return data_get($list, 'jingdong_pop_afs_soa_refundapply_queryPageList_responce.queryResult.result', []);
    }
}
