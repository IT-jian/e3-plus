<?php


namespace App\Services\Adaptor\Jingdong\Api;


use App\Services\Platform\Jingdong\Client\Jos\Request\GetVenderCommentsForJosRequest;

class VenderComments extends BaseApi
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
        $end = $params['end_modified'];

        $request = new GetVenderCommentsForJosRequest();
        $request->setBeginTime($start);
        $request->setEndTime($end);
        $request->setPage($page);
        $request->setPageSize($pageSize);

        $response = $this->request($request);

        return data_get($response, 'jingdong_pop_PopCommentJsfService_getVenderCommentsForJos_responce', []);
    }

    public function findByOrderId($orderId)
    {
        $request = new GetVenderCommentsForJosRequest();
        $request->setOrderIds($orderId);

        $response = $this->request($request);

        return data_get($response, 'jingdong_pop_PopCommentJsfService_getVenderCommentsForJos_responce.comments', []);
    }
}
