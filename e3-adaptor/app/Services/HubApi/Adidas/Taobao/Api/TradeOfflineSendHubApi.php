<?php


namespace App\Services\HubApi\Adidas\Taobao\Api;


use App\Facades\TopClient;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Taobao\Client\Top\Request\LogisticsOfflineSendRequest;

/**
 * 01-发货回写
 *
 * Class TradeOfflineSendHubApi
 * @package App\Services\HubApi\Adidas\Taobao\Api
 *
 * @author linqihai
 * @since 2020/1/2 10:45
 */
class TradeOfflineSendHubApi extends BaseApi
{
    // 必填字段
    protected $notNullFields = ['deal_code', 'shipping_sn', 'shipping_code', 'shop_code'];

    public function proxy()
    {
        if (isset($this->data['deal_type']) && 2 == $this->data['deal_type']) { // 换货单回写
            $exchangeApi = new ExchangeOfflineSendHubApi();
            $exchangeApi->setData($this->data);
            $exchangeApi->check();

            return $exchangeApi->proxy();
        } else { // 订单回写
            $request = new LogisticsOfflineSendRequest();

            $request->setTid($this->data['deal_code']);
            $request->setOutSid($this->data['shipping_sn']);
            $request->setCompanyCode($this->data['shipping_code']);
            $request->setIsSplit($this->data['is_split']);
            $request->setSubTid($this->data['sub_deal_code']);

            $result = TopClient::shop($this->data['shop_code'])->execute($request);

            return $this->responseSimple($result);
        }
    }

    public function isSuccess($response)
    {
        return data_get($response, 'logistics_offline_send_response.shipping.is_success', false);
    }

    /**
     * 处理报错
     *
     * @param $response
     * @return int|void
     */
    public function parseErrorCode($response)
    {
        $subCode = $response['sub_code'] ?? '';
        if ($subCode && in_array($subCode, ['B150', 'CD01', 'CD11', 'CD28', 'isp.top-remote-connection-timeout'])) {
            return self::ERROR_CODE_RETRY;
        }

        return self::ERROR_CODE_FAIL;
    }
}
