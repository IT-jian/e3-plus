<?php


namespace App\Services\Adaptor\Jingdong\Api;


use App\Services\Platform\Jingdong\Client\Jos\Request\SkuReadFindSkuByIdRequest;
use App\Services\Platform\Jingdong\Client\Jos\Request\SkuReadSearchSkuListRequest;

class Sku extends BaseApi
{
    protected $shop;

    protected $fields = 'sku,wareId,skuId,status,saleAttrs,features,jdPrice,outerId,barCode,categoryId,imgTag,logo,skuName,stockNum,wareTitle,fixedDeliveryTime,relativeDeliveryTime,parentId,modified,created,multiCateProps,props,capacity';

    public function __construct($shop)
    {
        $this->shop = $shop;
    }

    /**
     * 查询单个
     *
     * @param $skuId
     * @return array|mixed
     */
    public function find($skuId)
    {
        $request = new SkuReadFindSkuByIdRequest();
        $request->setSkuId($skuId);
        $fields = $this->fields;
        $request->setField($fields);
        $response = $this->request($request);

        return data_get($response, 'jingdong_sku_read_findSkuById_responce.sku', []);
    }

    /**
     * 查询多个
     *
     * @param $skuIds
     * @param string $fields
     * @return mixed
     */
    public function findMulti($skuIds, $fields = '')
    {
        $request = new SkuReadSearchSkuListRequest();
        $request->setSkuId($skuIds);
        if (empty($fields)) {
            $fields = $this->fields;
        }
        $request->setField($fields);
        $response = $this->request($request);

        return data_get($response, 'jingdong_sku_read_searchSkuList_responce.page.data', []);
    }
}
