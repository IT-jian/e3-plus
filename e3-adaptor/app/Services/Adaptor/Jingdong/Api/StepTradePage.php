<?php


namespace App\Services\Adaptor\Jingdong\Api;


use App\Services\Platform\Jingdong\Client\Jos\Request\GetPresaleOrderByPageRequest;

class StepTradePage extends BaseApi
{
    protected $shop;

    public function __construct($shop)
    {
        $this->shop = $shop;
    }

    /**
     * @param $params
     * @return mixed
     */
    public function page($params)
    {
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['page_size']) ? $params['page_size'] : 30;
        $start = $params['start_modified'];
        $orderStatusItem = $params['status'] ?? 1;
        $end = $params['end_modified'];

        $beginIndex = (($page - 1) * $pageSize) + 1;

        $request = new GetPresaleOrderByPageRequest();
        $request->setBeginIndex($beginIndex);
        $request->setEndIndex($pageSize);
        $request->setStartTime($start);
        $request->setEndTime($end);
        $request->setOrderStatusItem($orderStatusItem);

        $list = $this->request($request);

        return data_get($list, 'jingdong_presale_order_updateOrder_getPresaleOrderByPage_responce.returnType.data', []);
    }

    public function find($orderId)
    {
        $request = new GetPresaleOrderByPageRequest();
        $request->setOrderId($orderId);
        $list = $this->request($request);

        return data_get($list, 'jingdong_presale_order_updateOrder_getPresaleOrderByPage_responce.returnType.data', []);
    }
}
