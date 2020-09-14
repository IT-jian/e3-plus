<?php


namespace App\Services\HubApi\Adidas\Taobao\Api;


use App\Facades\TopClient;
use App\Models\Sys\Shop;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Taobao\Client\Top\Request\ItemSkuGetRequest;

/**
 * 18-单个sku信息查询
 *
 * Class ItemSkuGetHubApi
 * @package App\Services\HubApi\Adidas\Taobao\Api
 */
class ItemSkuGetHubApi extends BaseApi
{
    protected $notNullFields = ['shop_code', 'sku_id'];

    /**
     * @return array
     *
     * @author linqihai
     * @since 2020/5/27 16:29
     */
    public function proxy()
    {
        $shop = Shop::where('code', $this->data['shop_code'])->firstOrFail();
        $request = new ItemSkuGetRequest();
        $request->setSkuId($this->data['sku_id']);
        $request->setFields('sku_id,outer_id,quantity,modified');

        $result = TopClient::shop($shop['code'])->execute($request);

        $sku = data_get($result, 'item_sku_get_response.sku', []);
        if (empty($sku)) {
            return $this->fail($result);
        }

        $formatSku = [
            'sku_id'   => $sku['sku_id'],
            // 'outer_id' => $sku['outer_id'] ?? '',
            'quantity' => $sku['quantity'] ?? 0,
            'modified' => $sku['modified'] ?? '',
        ];

        return $this->success($formatSku);
    }
}
