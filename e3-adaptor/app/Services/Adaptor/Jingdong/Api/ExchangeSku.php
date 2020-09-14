<?php


namespace App\Services\Adaptor\Jingdong\Api;


use App\Services\Platform\Jingdong\Client\Jos\Request\SkuReadFindSkuByIdRequest;
use App\Services\Platform\Jingdong\Client\Jos\Request\WareReadFindWareByIdRequest;

class ExchangeSku extends BaseApi
{
    protected $shop;

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
        $fields = 'wareId,skuId,outerId,barCode,skuName,multiCateProps,saleAttrs,wareTitle';
        $request = new SkuReadFindSkuByIdRequest();
        $request->setSkuId($skuId);
        $request->setField($fields);
        $response = $this->request($request);

        $sku = data_get($response, 'jingdong_sku_read_findSkuById_responce.sku', []);
        if ($sku['wareId']) {
            $wareFields = "wareId,itemNum";
            $request = new WareReadFindWareByIdRequest();
            $request->setWareId($sku['wareId']);
            $request->setField($wareFields);
            $response = $this->request($request);
            $ware = data_get($response, 'jingdong_ware_read_findWareById_responce.ware', []);
            $sku['itemNum'] = $ware['itemNum'];
        }

        return $sku;
    }
}