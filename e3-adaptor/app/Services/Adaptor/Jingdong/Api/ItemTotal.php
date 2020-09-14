<?php


namespace App\Services\Adaptor\Jingdong\Api;


use App\Services\Platform\Jingdong\Client\Jos\Request\WareReadSearchWare4ValidRequest;

class ItemTotal extends BaseApi
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
    public function count($params)
    {
        $request = new WareReadSearchWare4ValidRequest();
        $request->setStartModifiedTime($params['start_modified']);
        $request->setEndModifiedTime($params['end_modified']);
        $request->setPageNo(1);
        $request->setPageSize(1);
        $fields = ['wareId'];
        $request->setField($fields);

        $response = $this->request($request);

        return data_get($response, 'jingdong_ware_read_searchWare4Valid_responce.page.totalItem', 0);
    }
}
