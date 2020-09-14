<?php


namespace App\Services\Adaptor\Jingdong\Api;


use App\Services\Platform\Jingdong\Client\Jos\Request\RefundApplyQueryPageListRequest;

/**
 * 查询退款信息
 *
 * Class RefundApplyByOrderId
 * @package App\Services\Adaptor\Jingdong\Api
 */
class RefundApplyQuery extends BaseApi
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
    public function find($params)
    {
        $id = $params['id'] ?? 0;
        $orderId = $params['order_id'] ?? 0;
        if (empty($id) && empty($orderId)){
            throw new \InvalidArgumentException('need id or order_id');
        }
        $request = new RefundApplyQueryPageListRequest();
        if ($id) {
            $request->setIds($id);
        }
        if ($orderId) {
            $request->setOrderId($orderId);
        }
        $list = $this->request($request);

        return data_get($list, 'jingdong_pop_afs_soa_refundapply_queryPageList_responce.queryResult.result', []);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function page($params)
    {
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['page_size']) ? $params['page_size'] : 50;
        $start = $params['start_modified'];
        $end = $params['end_modified'];

        $request = new RefundApplyQueryPageListRequest();
        $request->setApplyTimeStart($start);
        $request->setApplyTimeEnd($end);
        // $request->setStatus(0); 仅查询仅退款
        $request->setPageIndex($page);
        $request->setPageSize($pageSize);

        $response = $this->request($request);

        return data_get($response, 'jingdong_pop_afs_soa_refundapply_queryPageList_responce.queryResult.result', []);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function pageByCheck($params)
    {
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['page_size']) ? $params['page_size'] : 50;
        $start = $params['start_modified'];
        $end = $params['end_modified'];

        $request = new RefundApplyQueryPageListRequest();
        $request->setCheckTimeStart($start);
        $request->setCheckTimeEnd($end);
        // $request->setStatus(0); 仅查询仅退款
        $request->setPageIndex($page);
        $request->setPageSize($pageSize);

        $response = $this->request($request);

        return data_get($response, 'jingdong_pop_afs_soa_refundapply_queryPageList_responce.queryResult.result', []);
    }
}
