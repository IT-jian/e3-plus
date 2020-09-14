<?php


namespace App\Services\HubApi\Adidas\Jingdong\Api;


use App\Facades\JosClient;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Exceptions\PlatformServerSideException;
use App\Services\Platform\Jingdong\Client\Jos\Request\LogisticsOfflineSendRequest;

/**
 * 01-发货回写
 *
 * Class TradeOfflineSendHubApi
 * @package App\Services\HubApi\Adidas\Jingdong\Api
 *
 * @author linqihai
 * @since 2020/1/2 10:45
 */
class TradeOfflineSendHubApi extends BaseApi
{
    protected $notNullFields = ['deal_code', 'shipping_sn', 'shipping_code', 'shop_code'];

    public function proxy()
    {
        if (isset($this->data['deal_type']) && 2 == $this->data['deal_type']){ // 换货单回写
            // 查询京东服务单
            $exchangeApi = new ExchangeOfflineSendHubApi();
            $exchangeApi->setData($this->data);

            return $exchangeApi->proxy();
        } else {
            $request = new LogisticsOfflineSendRequest();
            $shipWay = $this->getShipWay($this->data['shipping_code']);

            $request->setOrderId($this->data['deal_code']);
            $request->setLogiNo($this->data['shipping_sn']);
            $request->setLogiCoprId($shipWay['id']);

            $result = JosClient::shop($this->data['shop_code'])->execute($request);

            return $this->responseSimple($result);
        }
    }

    public function isSuccess($response)
    {
        return data_get($response, 'jingdong_pop_order_shipment_responce.sopjosshipment_result.success', false);
    }

    public function getShipWay($shippingCode)
    {
        $shippingCode = strtolower($shippingCode);
            $map = [
                'yzkdbg' => ['id' => 465, 'name' => '邮政EMS'],
                'ems' => ['id' => 465, 'name' => '邮政EMS'],
                'sf' => ['id' => 467, 'name' => '顺丰快递'],
            ];

        return $map[$shippingCode];
    }

    /**
     * 处理报错
     *
     * @param $response
     * @return int|void
     */
    public function parseErrorCode($response)
    {
        $errorCode = data_get($response, 'jingdong_pop_order_shipment_responce.sopjosshipment_result.errorCode', false);
        if ($errorCode && in_array($errorCode, ['999999', '99999999'])) {
            return self::ERROR_CODE_RETRY;
        }

        return self::ERROR_CODE_FAIL;
    }
}
