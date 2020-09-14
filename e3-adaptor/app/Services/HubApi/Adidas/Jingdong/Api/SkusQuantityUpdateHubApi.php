<?php


namespace App\Services\HubApi\Adidas\Jingdong\Api;


use App\Facades\JosClient;
use App\Models\Sys\Shop;
use App\Services\Adaptor\Jingdong\Api\Sku;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Jingdong\Client\Jos\Request\StockWriteUpdateSkuStock;

/**
 * 14 - 库存同步
 *
 * Class SkusQuantityUpdateHubApi
 *
 * @package App\Services\HubApi\Adidas\Jingdong\Api
 */
class SkusQuantityUpdateHubApi extends BaseApi
{
    public function proxy()
    {
        $shop = Shop::where('code', $this->data['shop_code'])->firstOrFail();

        $requests = [];
        $skus = $this->data['data'];
        foreach ($skus as $sku) {
            $request = new StockWriteUpdateSkuStock();
            $skuId = $sku['sku_id'];
            $request->setSkuId($skuId);
            $quantity = $sku['quantity'];
            // 增量同步
            if (2 == $sku['type']) {
                $platformSku = (new Sku($shop))->find($skuId);
                if ($currentStock = $platformSku['stockNum'] ?? 0) {
                    $quantity = $currentStock + $quantity;
                }
            }
            $request->setStockNum($quantity);
            $requests[$skuId] = $request;
        }

        $responses = JosClient::shop($shop['code'])->execute($requests);

        $isSuccess = true;
        foreach ($responses as $response) {
            if (!$this->isSuccess($response)) {
                $isSuccess = false;
            };
        }

        if ($isSuccess) {
            return $this->success($responses);
        }
        return $this->fail($responses);
    }

    public function isSuccess($response)
    {
        return data_get($response, 'jingdong_stock_write_updateSkuStock_responce.success', false);
    }
}
