<?php


namespace App\Services\Adaptor\Jingdong\Api;


use App\Services\Platform\Jingdong\Client\Jos\Request\AscSyncListRequest;

class RefundUpdate extends BaseApi
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

        $request = new AscSyncListRequest();
        $request->setBuId($this->shop['seller_nick']);
        $request->setOperatePin('adaptor');
        $request->setOperateNick('adaptor');
        if (isset($params['status']) && !empty($params['status'])) {
            $request->setServiceStatus($params['status']);
        }
        $request->setUpdateTimeBegin($start);
        $request->setUpdateTimeEnd($end);
        $request->setPageNumber($page);
        $request->setPageSize($pageSize);
        $response = $this->request($request);

        return data_get($response, 'jingdong_asc_sync_list_responce.pageResult.data', []);
    }

    public function freightPage($params)
    {
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['page_size']) ? $params['page_size'] : 10;
        $start = $params['start_modified'];
        $end = $params['end_modified'];

        $request = new AscSyncListRequest();
        $request->setBuId($this->shop['seller_nick']);
        $request->setOperatePin('adaptor');
        $request->setOperateNick('adaptor');
        if (isset($params['status']) && !empty($params['status'])) {
            $request->setServiceStatus($params['status']);
        }
        $request->setFreightUpdateDateBegin($start);
        $request->setFreightUpdateDateEnd($end);
        $request->setPageNumber($page);
        $request->setPageSize($pageSize);
        $response = $this->request($request);

        return data_get($response, 'jingdong_asc_sync_list_responce.pageResult.data', []);
    }
}
