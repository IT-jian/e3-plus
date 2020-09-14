<?php


namespace App\Services\Adaptor\Jingdong\Api;


use App\Services\Platform\Jingdong\Client\Jos\Request\AscQueryViewRequest;
use App\Services\Platform\Jingdong\Client\Jos\Request\AscServiceAndRefundViewRequest;

class RefundDetail extends BaseApi
{
    protected $shop;

    public function __construct($shop)
    {
        $this->shop = $shop;
    }

    public function multi($refunds)
    {
        $result = [];
        foreach ($refunds as $refund) {
            $request = new AscQueryViewRequest();
            $request->setServiceId($refund['service_id']);
            $request->setOrderId($refund['order_id']);
            $request->setBuId($this->shop['seller_nick']);
            $request->setOperatePin('adaptor');
            $request->setOperateNick('adaptor');
            $requests[$refund['service_id']] = $request;
        }
        $responses = $this->request($requests);
        foreach ($responses as $key => $response) {
            $result[$key] = data_get($response, 'jingdong_asc_query_view_responce.result.data', []);
        }

        return $result;
    }
    /**
     * 查询单个
     *
     * @param $serviceId
     * @param $orderId
     * @return array|mixed
     */
    public function find($serviceId, $orderId)
    {
        $requests = [];
        // 明细
        $request = new AscQueryViewRequest();
        $request->setServiceId($serviceId);
        $request->setOrderId($orderId);
        $request->setBuId($this->shop['seller_nick']);
        $request->setOperatePin('adaptor');
        $request->setOperateNick('adaptor');
        $requests['detail'] = $request;
        // 退款
        $request = new AscServiceAndRefundViewRequest();
        $request->setOrderId($orderId);

        $response = $this->request($request);

        return data_get($response, 'jingdong_asc_query_view_responce.result.data', []);
    }
}
