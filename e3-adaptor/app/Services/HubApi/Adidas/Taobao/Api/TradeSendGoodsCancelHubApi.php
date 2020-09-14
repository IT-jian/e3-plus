<?php


namespace App\Services\HubApi\Adidas\Taobao\Api;


use App\Facades\TopClient;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Taobao\Client\Top\Request\RdcAligeniusSendgoodsCancelRequest;

/**
 * AG 取消发货
 *
 * Class TradeSendGoodsCancelHubApi
 * @package App\Services\HubApi\Adidas\Taobao\Api
 *
 * @author linqihai
 * @since 2020/05/22 11:45
 */
class TradeSendGoodsCancelHubApi extends BaseApi
{
    // 必填字段
    protected $notNullFields = ['deal_code', 'warehouse_status', 'refund_id', 'shop_code'];

    public function proxy()
    {
        $request = new RdcAligeniusSendgoodsCancelRequest();
        $params = [
            'refund_id'    => $this->data['refund_id'],
            'tid'          => $this->data['deal_code'],
            'status'       => 1 == $this->data['warehouse_status'] ? 'SUCCESS' : 'FAIL',
        ];

        $request->setParam(json_encode($params));

        $result = TopClient::shop($this->data['shop_code'])->execute($request);

        return $this->responseSimple($result);
    }

    public function isSuccess($response)
    {
        return data_get($response, 'rdc_aligenius_sendgoods_cancel_response.result.success', false);
    }
}