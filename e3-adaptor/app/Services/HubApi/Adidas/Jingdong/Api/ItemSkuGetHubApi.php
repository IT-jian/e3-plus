<?php


namespace App\Services\HubApi\Adidas\Jingdong\Api;


use App\Facades\JosClient;
use App\Models\Sys\Shop;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Jingdong\Client\Jos\Request\SkuReadFindSkuByIdRequest;

/**
 * 18-SKU详情查询
 *
 * Class ItemsInventoryGetHubApi
 *
 * @package App\Services\HubApi\Adidas\Jingdong\Api
 */
class ItemSkuGetHubApi extends BaseApi
{
    protected $notNullFields = ['shop_code', 'sku_id'];

    public function proxy()
    {
        $shop = Shop::where('code', $this->data['shop_code'])->firstOrFail();

        $request = new SkuReadFindSkuByIdRequest();
        $request->setSkuId($this->data['sku_id']);
        $request->setField('skuId,outerId,stockNum,modified');

        $result = JosClient::shop($shop['code'])->execute($request);

        $sku = data_get($result, 'jingdong_sku_read_findSkuById_response.sku', []);
        if (empty($sku)) {
            return $this->fail($result);
        }

        $formatSku = [
            'sku_id'   => $sku['skuId'],
            // 'outer_id' => $sku['outer_id'] ?? '',
            'quantity' => $sku['stockNum'] ?? 0,
            'modified' => $sku['modified'] ?? '',
        ];

        return $this->success($formatSku);
    }
}
