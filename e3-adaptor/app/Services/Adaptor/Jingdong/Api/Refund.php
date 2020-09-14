<?php


namespace App\Services\Adaptor\Jingdong\Api;


use App\Services\Platform\Jingdong\Client\Jos\Request\AscReceiveCountRequest;
use App\Services\Platform\Jingdong\Client\Jos\Request\AscReceiveListRequest;

class Refund extends BaseApi
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
        $pageSize = isset($params['page_size']) ? $params['page_size'] : 10;
        $start = $params['start_modified'];
        $end = $params['end_modified'];

        $request = new AscReceiveListRequest();
        $request->setBuId($this->shop['seller_nick']);
        $request->setOperatePin('adaptor');
        $request->setOperateNick('adaptor');
        $request->setApplyTimeBegin($start);
        $request->setApplyTimeEnd($end);
        $request->setPageNumber($page);
        $request->setPageSize($pageSize);

        $response = $this->request($request);

        return data_get($response, 'jingdong_asc_receive_list_responce.pageResult.data', []);
    }

    /**
     * 查询单个
     *
     * @param $serviceId
     * @param $orderId
     * @return array|mixed
     */
    public function find($serviceId = '', $orderId = '')
    {
        if (empty($serviceId) && empty($orderId)){
            throw new \InvalidArgumentException('need service_id or order_id');
        }
        $request = new AscReceiveListRequest();
        if ($serviceId) {
            $request->setServiceId($serviceId);
        }
        if ($orderId) {
            $request->setOrderId($orderId);
        }
        $request->setBuId($this->shop['seller_nick']);
        $request->setOperatePin('adaptor');
        $request->setOperateNick('adaptor');
        $response = $this->request($request);

        return data_get($response, 'jingdong_asc_receive_list_responce.pageResult.data', []);
    }

    /**
     * 统计时间段内的待收货服务单
     *
     * @param $params
     * @return int
     */
    public function count($params)
    {
        $start = $params['start_modified'];
        $end = $params['end_modified'];

        $request = new AscReceiveCountRequest();
        $request->setBuId($this->shop['seller_nick']);
        $request->setOperatePin('adaptor');
        $request->setOperateNick('adaptor');
        $request->setApplyTimeBegin($start);
        $request->setApplyTimeEnd($end);
        $response = $this->request($request);

        return data_get($response, 'jingdong_asc_receive_count_responce.result.data', 0);
    }
}
