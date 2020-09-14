<?php


namespace App\Services\HubApi\Adidas\Jingdong\Api;


use App\Facades\JosClient;
use App\Models\JingdongRefund;
use App\Models\Sys\Shop;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Jingdong\Client\Jos\Request\AscCommonCancelRequest;

/**
 * 05-拒绝收货 -- 取消服务单
 *
 * Class RefundReturnGoodsRefuseHubApi
 *
 * @package App\Services\HubApi\Adidas\Jingdong\Api
 */
class RefundReturnGoodsRefuseHubApi extends BaseApi
{
    public function proxy()
    {
        $params = $this->data;
        $shop = Shop::where('code', $this->data['shop_code'])->firstOrFail();

        $request = new AscCommonCancelRequest();
        $request->setBuId($shop['seller_nick']);
        $request->setServiceId($params['refund_id']);
        $request->setOrderId($params['deal_code']);
        $request->setApproveNotes($params['refund_remark']);
        $request->setSysVersion($params['refund_version']);

        $result = JosClient::shop($shop['code'])->execute($request);

        return $this->responseSimple($result);
    }

    public function isSuccess($response)
    {
        return data_get($response, 'jingdong_asc_common_cancel_responce.result.success', false);
    }
}
