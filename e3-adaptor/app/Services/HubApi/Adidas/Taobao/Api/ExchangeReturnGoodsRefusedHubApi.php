<?php


namespace App\Services\HubApi\Adidas\Taobao\Api;


use App\Facades\TopClient;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Taobao\Client\Top\Request\ExchangeReturnGoodsRefuseRequest;

/**
 * 09-换货拒绝确认收货
 * 
 * Class ExchangeReturnGoodsRefusedHubApi
 * @package App\Services\HubApi\Adidas\Taobao\Api
 *
 * @author linqihai
 * @since 2020/1/2 11:24
 */
class ExchangeReturnGoodsRefusedHubApi extends BaseApi
{
    protected $notNullFields = ['refund_id', 'refund_reason_id', 'shop_code', 'refund_version', 'deal_code'];

    public function proxy()
    {
        $request = new ExchangeReturnGoodsRefuseRequest();
        $request->setDisputeId($this->data['refund_id']);
        $request->setLeaveMessage($this->data['refund_remark'] ?? null);
        $request->setSellerRefuseReasonId($this->data['refund_reason_id']);

        $result = TopClient::shop($this->data['shop_code'])->execute($request);

        return $this->responseSimple($result);
    }

    public function isSuccess($response)
    {
        return data_get($response, 'tmall_exchange_returngoods_refuse_response.result.success', false);
    }
}