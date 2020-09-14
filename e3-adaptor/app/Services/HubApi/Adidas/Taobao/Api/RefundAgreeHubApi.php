<?php


namespace App\Services\HubApi\Adidas\Taobao\Api;

use App\Facades\TopClient;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Taobao\Client\Top\Request\RpReturnGoodsAgreeRequest;

/**
 * 02-同意退货
 *
 * Class RefundAgreeHubApi
 * @package App\Services\HubApi\Adidas\Taobao\Api
 *
 * @author linqihai
 * @since 2020/1/2 10:44
 */
class RefundAgreeHubApi extends BaseApi
{
    protected $notNullFields = ['refund_id', 'shop_code'];

    public function proxy()
    {
        $request = new RpReturnGoodsAgreeRequest();
        $request->setRefundId($this->data['refund_id']);
        if (isset($this->data['refund_phase'])) {
            $request->setRefundPhase($this->data['refund_phase']);
        }
        if (isset($this->data['refund_version'])) {
            $request->setRefundVersion($this->data['refund_version']);
        }
        if (isset($this->data['seller_address_id'])) {
            $request->setSellerAddressId($this->data['seller_address_id']);
        }
        if (isset($this->data['post_fee_bear_role'])) {
            $request->setPostFeeBearRole($this->data['post_fee_bear_role']);
        }
        if (isset($this->data['refund_remark'])) {
            $request->setRemark($this->data['refund_remark']);
        }

        $result = TopClient::shop($this->data['shop_code'])->execute($request);

        return $this->responseSimple($result);
    }

    public function isSuccess($response)
    {
        return data_get($response, 'rp_returngoods_agree_response.is_success', false);
    }
}