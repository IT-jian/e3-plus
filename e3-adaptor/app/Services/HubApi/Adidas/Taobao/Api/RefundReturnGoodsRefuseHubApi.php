<?php


namespace App\Services\HubApi\Adidas\Taobao\Api;

use App\Facades\TopClient;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Taobao\Client\Top\Request\RpReturnGoodsRefuseRequest;

/**
 * 05-拒绝退款(收货之后拒绝)
 *
 * Class RefundReturnGoodsRefuseHubApi
 * @package App\Services\HubApi\Adidas\Taobao\Api
 *
 * @author linqihai
 * @since 2020/1/2 10:44
 */
class RefundReturnGoodsRefuseHubApi extends BaseApi
{
    protected $notNullFields = ['refund_id', 'refund_phase', 'refund_version', 'refuse_proof', 'shop_code'];

    public function proxy()
    {
        $request = new RpReturnGoodsRefuseRequest();

        $request->setRefundId($this->data['refund_id']);
        $request->setRefundPhase($this->data['refund_phase']);
        $request->setRefundVersion($this->data['refund_version']);
        $request->setRefuseProof('@' . $this->data['refuse_proof']);
        if (isset($this->data['refund_reason_id'])) {
            $request->setRefuseReasonId($this->data['refund_reason_id']);
        }

        $result = TopClient::shop($this->data['shop_code'])->execute($request);

        return $this->responseSimple($result);
    }

    public function isSuccess($response)
    {
        return data_get($response, 'rp_returngoods_refuse_response.result', false);
    }
}