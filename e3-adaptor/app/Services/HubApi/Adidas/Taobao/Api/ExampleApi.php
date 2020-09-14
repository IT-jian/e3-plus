<?php


namespace App\Services\HubApi\Adidas\Taobao\Api;


use App\Facades\TopClient;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Taobao\Client\Top\Request\RpReturnGoodsAgreeRequest;

class ExampleApi extends BaseApi
{
    public function proxy()
    {
        $request = new RpReturnGoodsAgreeRequest();
        $request->setRefundId($this->data['refund_id']);

        $result = TopClient::shop($this->data['shop_code'])->execute($request);

        return [
            'status' => data_get($result, 'rp_returngoods_agree_response.is_success', false),
            'data' => [],
            'message' => 'success',
        ];
    }
}