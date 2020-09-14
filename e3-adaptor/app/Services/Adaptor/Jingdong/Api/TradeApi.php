<?php


namespace App\Services\Adaptor\Jingdong\Api;


use App\Services\Platform\Jingdong\Client\Jos\Request\PopOrderGetRequest;

class TradeApi extends BaseApi
{
    protected $shop;

    public function __construct($shop)
    {
        $this->shop = $shop;
    }

    /**
     * @param $tid
     * @return mixed
     */
    public function find($tid)
    {
        $request = new PopOrderGetRequest();
        $request->setOrderId($tid);

        $response = $this->request($request);

        return data_get($response, 'jingdong_pop_order_get_responce.orderDetailInfo.orderInfo', []);
    }

    public function findMulti($tids)
    {
        $requests = [];
        foreach ($tids as $tid) {
            $request = new PopOrderGetRequest();
            $requests[$tid] = $request->setOrderId($tid);
        }

        $responses = $this->request($requests);

        foreach ($responses as $tid => $response) {
            $responses[$tid] = data_get($response, 'jingdong_pop_order_get_responce.orderDetailInfo.orderInfo', []);
        }
        return $responses;
    }
}
