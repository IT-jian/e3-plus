<?php


namespace App\Services\Adaptor\Jingdong\Api;


use App\Services\Platform\Jingdong\Client\Jos\Request\SkuReadSearchSkuListRequest;

class SkuTotal extends BaseApi
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
        $request = new SkuReadSearchSkuListRequest();
        $request->setStartCreatedTime($params['start_created']);
        $request->setEndCreatedTime($params['end_created']);
        $request->setPageNo(1);
        $request->setPageSize($params['page_size'] ?? 50);
        $fields = ['skuId'];
        $request->setField($fields);

        $response = $this->request($request);

        return data_get($response, 'jingdong_sku_read_searchSkuList_responce.page.totalItem', 0);
    }
}
