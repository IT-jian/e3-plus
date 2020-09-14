<?php


namespace App\Services\HubApi\Adidas\Taobao\Api;


use App\Facades\TopClient;
use App\Models\Sys\Shop;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Taobao\Client\Top\Request\ItemSellerGetRequest;
use App\Services\Platform\Taobao\Client\Top\Request\ItemsOnsaleGetRequest;

/**
 * 13-商店sku库存查询
 *
 * Class ItemsInventoryGetHubApi
 * @package App\Services\HubApi\Adidas\Taobao\Api
 */
class ItemsInventoryGetHubApi extends BaseApi
{
    protected $notNullFields = ['shop_code'];

    /**
     * @return array
     *
     * @author linqihai
     * @since 2020/5/27 16:29
     */
    public function proxy()
    {
        $params = $this->data;
        $shop = Shop::where('code', $this->data['shop_code'])->firstOrFail();

        $fields = ['num_iid'];

        $request = new ItemsOnsaleGetRequest();
        $request->setPageNo($params['page_no'] ?? 1);
        $request->setPageSize($params['page_size'] ?? 50);
        $request->setFields($fields);

        $result = TopClient::shop($shop['code'])->execute($request);

        $itemsPage = data_get($result, 'items_onsale_get_response.items.item', []);
        if (empty($itemsPage)) {
            return $this->success([]);
        }
        $numIids = [];
        foreach ($itemsPage as $item) {
            $numIids[] = $item['num_iid'];
        }
        $items = $this->getItemsDetail($numIids, $shop);

        return $this->success($items);
    }

    /**
     * 查询货号对应sku信息
     *
     * @param $numIids
     * @param $shop
     * @return array
     *
     * @author linqihai
     * @since 2020/5/27 16:29
     */
    private function getItemsDetail($numIids, $shop)
    {
        $requests = $wareSkus = $multiPageRequests = [];
        // status 1:上架 2:下架 4:删除
        $fields = ['num_iid', 'outer_id', 'approve_status', 'title', 'sku'];
        $items = [];
        // 10 个并发请求一次
        foreach (array_chunk($numIids, 10) as $chunkNumIids) {
            foreach ($chunkNumIids as $key => $numIid) {
                $request = new ItemSellerGetRequest();
                $request->setFields($fields);
                $request->setNumIid($numIid);
                $requests[$numIid] = $request;
            }
            $responses = TopClient::shop($shop['code'])->execute($requests);
            foreach ($responses as $numIid => $response) {
                if ($item = data_get($response, 'item_seller_get_response.item', [])) {
                    $formatSkus = [];
                    $skus = data_get($item, 'skus.sku', []);
                    if (empty($skus)) {
                        $item['skus'] = [];
                        continue;
                    }
                    foreach ($skus as $sku) {
                        $formatSkus[] = [
                            'created'  => $sku['created'],
                            'modified' => $sku['modified'],
                            'price'    => $sku['price'],
                            'outer_id' => $sku['outer_id'],
                            'quantity' => $sku['quantity'],
                            'sku_id'   => $sku['sku_id'],
                        ];
                    }
                    $item['skus']['sku'] = $formatSkus;
                }
                $items[] = $item;
            }
        }

        return $items;
    }

    public function isSuccess($response)
    {
        $responseSuccess = data_get($response, 'total_results', []);

        return $responseSuccess;
    }
}
