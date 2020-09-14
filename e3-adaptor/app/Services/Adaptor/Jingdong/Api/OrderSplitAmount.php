<?php


namespace App\Services\Adaptor\Jingdong\Api;


use App\Facades\JosClient;
use App\Services\Platform\Jingdong\Client\Jos\Request\QueryOrderSplitAmountByQueryArrRequest;

/**
 * 京东订单交易号，查询明细金额分摊信息
 *
 * Class OrderSplitAmount
 * @package App\Services\Adaptor\Jingdong\Api
 */
class OrderSplitAmount
{
    private $shop;

    public function __construct($shop)
    {
        $this->shop = $shop;
    }

    /**
     * @param $orderId
     * @param $systemKey
     * @param $systemName
     * @return mixed
     */
    public function find($orderId, $systemKey, $systemName)
    {
        $request = new QueryOrderSplitAmountByQueryArrRequest();
        $request->setId($orderId);
        $request->setSystemKey($systemKey);
        $request->setSystemName($systemName);
        $request->setQueryTypes('all');

        $response = JosClient::shop($this->shop['code'])->execute($request, $this->shop['access_token']);

        $jsonString = data_get($response, 'jingdong_queryOrderSplitAmountByQueryArr_responce.orderSplitAmountResult', '{}');

        return json_decode($jsonString, true);
    }

    /**
     * @param $orderIds
     * @param $systemKey
     * @param $systemName
     * @return mixed
     */
    public function findMulti($orderIds, $systemKey, $systemName)
    {
        $requests = [];
        foreach ($orderIds as $orderId) {
            $request = new QueryOrderSplitAmountByQueryArrRequest();
            $request->setSystemKey($systemKey);
            $request->setSystemName($systemName);
            $request->setQueryTypes('all');
            $requests[$orderId] = $request->setId($orderId);
        }
        $responses = JosClient::shop($this->shop['code'])->execute($requests, $this->shop['access_token']);
        foreach ($responses as $orderId => $response) {
            $jsonString = data_get($response, 'jingdong_queryOrderSplitAmountByQueryArr_responce.orderSplitAmountResult', '{}');
            $responses[$orderId] = json_decode($jsonString, true);
        }


        return $responses;
    }
}
