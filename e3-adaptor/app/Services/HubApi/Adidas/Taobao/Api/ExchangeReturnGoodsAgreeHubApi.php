<?php


namespace App\Services\HubApi\Adidas\Taobao\Api;


use App\Facades\TopClient;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Taobao\Client\Top\Request\ExchangeReturnGoodsAgreeRequest;

/**
 * 08-换货同意确认收货
 *
 * Class ExchangeReturnGoodsAgreeHubApi
 * @package App\Services\HubApi\Adidas\Taobao\Api
 *
 * @author linqihai
 * @since 2020/1/2 11:24
 */
class ExchangeReturnGoodsAgreeHubApi extends BaseApi
{
    protected $notNullFields = ['refund_id', 'shop_code'];

    public function proxy()
    {
        $request = new ExchangeReturnGoodsAgreeRequest();
        $request->setDisputeId($this->data['refund_id']);

        $result = TopClient::shop($this->data['shop_code'])->execute($request);

        return $this->responseSimple($result);
    }

    public function isSuccess($response)
    {
        return data_get($response, 'tmall_exchange_returngoods_agree_response.result.success', false);
    }

    public function parseErrorCode($response)
    {
        $subCode = $response['sub_code'] ?? '';
        if ($subCode && in_array($subCode, ['isp.top-remote-connection-timeout'])) {
            return self::ERROR_CODE_RETRY;
        }

        return self::ERROR_CODE_FAIL;
    }
}
