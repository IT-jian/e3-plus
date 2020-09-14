<?php


namespace App\Services\HubApi\Adidas\Taobao\Api;


use App\Facades\TopClient;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Taobao\Client\Top\Request\ExchangeConsigngoodsRequest;

/**
 * 01-发货回写 -- 换货单
 *
 * Class ExchangeOfflineSendHubApi
 * @package App\Services\HubApi\Adidas\Taobao\Api
 *
 * @author linqihai
 * @since 2020/05/22 11:45
 */
class ExchangeOfflineSendHubApi extends BaseApi
{
    // 必填字段
    protected $notNullFields = ['deal_type', 'shipping_sn', 'shipping_code', 'refund_id', 'shop_code'];

    public function proxy()
    {
        $request = new ExchangeConsigngoodsRequest();
        $request->setDisputeId($this->data['refund_id']);
        $request->setLogisticsType($this->data['logistics_type'] ?? 200);
        $request->setLogisticsNo($this->data['shipping_sn']);
        $request->setLogisticsCompanyName($this->shippingMap($this->data['shipping_code'])); // 快递公司名称

        $result = TopClient::shop($this->data['shop_code'])->execute($request);

        return $this->responseSimple($result);
    }

    public function isSuccess($response)
    {
        return data_get($response, 'tmall_exchange_consigngoods_response.result.success', false);
    }

    /**
     * 快递公司名称
     *
     * @param $code
     * @return mixed
     */
    public function shippingMap($code)
    {
        $map = [
            'SF' => '顺丰快递',
            'EMS' => '邮政EMS',
        ];

        return $map[$code] ?? $code;
    }

    public function parseErrorCode($response)
    {
        $subCode = $response['sub_code'] ?? '';
        if ($subCode && in_array($subCode, ['B150', 'CD01', 'CD11', 'CD28', 'isp.top-remote-connection-timeout'])) {
            return self::ERROR_CODE_RETRY;
        }

        return self::ERROR_CODE_FAIL;
    }
}
