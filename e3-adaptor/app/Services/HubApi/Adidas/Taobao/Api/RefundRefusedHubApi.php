<?php


namespace App\Services\HubApi\Adidas\Taobao\Api;

use App\Facades\TopClient;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Taobao\Client\Top\Request\RefundRefuseRequest;

/**
 * 03-拒绝退货
 *
 * Class RefundRefusedHubApi
 * @package App\Services\HubApi\Adidas\Taobao\Api
 *
 * @author linqihai
 * @since 2020/1/2 10:44
 */
class RefundRefusedHubApi extends BaseApi
{
    protected $notNullFields = ['refund_id', 'refund_phase', 'refund_version', 'refuse_proof', 'shop_code'];

    public function proxy()
    {
        $request = new RefundRefuseRequest();

        $request->setRefundId($this->data['refund_id']);
        $request->setRefuseMessage($this->data['refund_remark'] ?? '');
        if (isset($this->data['refund_phase'])) {
            $request->setRefundPhase($this->data['refund_phase']);
        }
        if (isset($this->data['refund_version'])) {
            $request->setRefundVersion($this->data['refund_version']);
        }
        if (isset($this->data['refuse_proof'])) {
            $request->setRefuseProof('@' . $this->data['refuse_proof']);
        }
        if (isset($this->data['refund_reason_id'])) {
            $request->setRefuseReasonId($this->data['refund_reason_id']);
        }

        $result = TopClient::shop($this->data['shop_code'])->execute($request);

        return $this->responseSimple($result);
    }

    public function isSuccess($response)
    {
        return data_get($response, 'refund_refuse_response.is_success', false);
    }
}