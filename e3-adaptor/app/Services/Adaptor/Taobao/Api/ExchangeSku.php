<?php


namespace App\Services\Adaptor\Taobao\Api;


use App\Facades\TopClient;
use App\Services\Platform\Taobao\Client\Top\Request\ItemSellerGetRequest;
use App\Services\Platform\Taobao\Client\Top\Request\ItemSkuGetRequest;

class ExchangeSku
{
    private $shop;

    public function __construct($shop)
    {
        $this->shop = $shop;
    }

    /**
     * @param $skuId
     * @return mixed
     *
     * @author linqihai
     * @since 2020/5/20 18:34
     */
    public function find($skuId)
    {
        // 查询sku详情
        $request = new ItemSkuGetRequest();
        $fields = 'num_iid,sku_id,outer_id,properties_name';
        $request = $request->setFields($fields);
        $request->setSkuId($skuId);
        $response = TopClient::shop($this->shop['code'])->execute($request, $this->shop['access_token']);
        $sku = data_get($response, 'item_sku_get_response.sku', []);
        if ($sku['num_iid']) { // 查询商品详情
            $itemFields = "num_iid,title,outer_id";
            $request = new ItemSellerGetRequest();
            $request->setNumIid($sku['num_iid']);
            $request->setFields($itemFields);
            $response = TopClient::shop($this->shop['code'])->execute($request, $this->shop['access_token']);
            $item = data_get($response, 'item_seller_get_response.item', []);
            $sku['outer_iid'] = $item['outer_id'];
            $sku['title'] = $item['title'];
        }

        return $sku;
    }
}
