<?php


namespace App\Services\Adaptor\Jingdong\Api;


use App\Services\Platform\Jingdong\Client\Jos\JosClient;
use App\Services\Platform\Jingdong\Client\Jos\Request\GetPresaleOrderCountRequest;

class StepTradeCount
{
    private $shop;

    public function __construct($shop)
    {
        $this->shop = $shop;
    }

    /**
     * @param $params
     * @return mixed
     */
    public function count($params)
    {
        $start = $params['start_modified'];
        $end = $params['end_modified'];
        $orderStatusItem = $params['status'] ?? 1;

        $request = new GetPresaleOrderCountRequest();
        $request->setStartTime($start);
        $request->setEndTime($end);
        $request->setOrderStatusItem($orderStatusItem);

        $list = JosClient::shop($this->shop['code'])->execute($request, $this->shop['access_token']);

        return data_get($list, 'jingdong_presale_order_updateOrder_getPresaleOrderCount_responce.returnType.data', 0);
    }
}
