<?php


namespace App\Services\HubApi\Adidas\Jingdong\Api;


use App\Facades\JosClient;
use App\Models\Sys\Shop;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Jingdong\Client\Jos\Request\SkuReadSearchSkuListRequest;
use App\Services\Platform\Jingdong\Client\Jos\Request\WareReadSearchWare4ValidRequest;

/**
 * 13-店铺sku库存 -- 搜索有效商品
 *
 * Class ItemsInventoryGetHubApi
 *
 * @package App\Services\HubApi\Adidas\Jingdong\Api
 */
class ItemsInventoryGetHubApi extends BaseApi
{
    public function proxy()
    {
        $params = $this->data;
        $shop = Shop::where('code', $this->data['shop_code'])->firstOrFail();

        $fields = ['wareId', 'outerId', 'wareStatus', 'title'];

        $request = new WareReadSearchWare4ValidRequest();
        $request->setPageNo($params['page_no'] ?? 1);
        $request->setPageSize($params['page_size'] ?? 50);
        $request->setField($fields);

        $result = JosClient::shop($shop['code'])->execute($request);

        $validWare = data_get($result, 'jingdong_ware_read_searchWare4Valid_responce.page.data', []);
        if (empty($validWare)) {
            return $this->success([]);
        }
        $items = $wares = $wareIds = [];
        foreach ($validWare as $item) {
            $wares[$item['wareId']] = [
                'num_iid' => $item['wareId'],
                'outer_id' => $item['outerId'],
                // 商品状态 -1：删除 1:从未上架 2:自主下架 4:系统下架 8:上架 513:从未上架待审 514:自主下架待审 516:系统下架待审 520:上架待审核 1028:系统下架审核失败
                'approve_status' => 8 == $item['wareStatus'] ? 'onsale' : 'instock',
                'title' => $item['title'] ?? ''
            ];
        }
        $wareIds = array_keys($wares);
        $skuMap = $this->getWareSkuMap($wareIds, $shop);
        foreach ($wares as $wareId => $ware) {
            if (isset($skuMap[$wareId])) {
                foreach ($skuMap[$wareId] as $sku) {
                    $skus[] = [
                        'created'  => $sku['created'],
                        'modified' => $sku['modified'],
                        'price'    => $sku['jdPrice'],
                        'outer_id' => $sku['outerId'],
                        'quantity' => $sku['stockNum'],
                        'sku_id'   => $sku['skuId'],
                    ];
                }
            } else {
                $skus = [];
            }
            $ware['skus']['sku'] = $skus;
            $items[] = $ware;
        }

        return $this->success($items);
    }

    /**
     * 查询货号对应sku信息
     *
     * @param $wareIds
     * @param $shop
     * @return array
     */
    private function getWareSkuMap($wareIds, $shop)
    {
        $requests = $wareSkus = $multiPageRequests = [];
        // status 1:上架 2:下架 4:删除
        $fields = ['wareId', 'status', 'created', 'modified', 'jdPrice', 'outerId', 'stockNum', 'skuId'];
        $pageSize = 100;
        foreach (array_chunk($wareIds, 10) as $key => $chunk) {
            $request = new SkuReadSearchSkuListRequest();
            $request->setWareId($chunk);
            $request->setField($fields);
            $request->setPageNo(1);
            $request->setPageSize($pageSize);
            $requests[$key] = $request;
        }
        $responses = JosClient::shop($shop['code'])->execute($requests);
        foreach ($responses as $key => $response) {
            $skus = data_get($response, 'jingdong_sku_read_searchSkuList_responce.page.data', []);
            foreach ($skus as $sku) {
                $wareSkus[$sku['wareId']][$sku['skuId']] = $sku;
            }
            $total = data_get($response, 'jingdong_sku_read_searchSkuList_responce.page.totalItem', 0);
            $pageTotal = ceil($total / $pageSize);
            if ($pageTotal > 1) { // 大于1页，则继续下载剩余数量
                $requestSource = $requests[$key];
                $wareIdSource = $requestSource->wareId;
                foreach (range(2, $pageTotal) as $pageNo) {
                    $request = new SkuReadSearchSkuListRequest();
                    $request->setWareId($wareIdSource);
                    $request->setField($fields);
                    $request->setPageNo($pageNo);
                    $request->setPageSize($pageSize);
                    $multiPageRequests[] = $request;
                }
            }
        }
        if (!empty($multiPageRequests)) {
            $multiPageResponses = JosClient::shop($shop['code'])->execute($multiPageRequests);
            foreach ($multiPageResponses as $multiPageResponse) {
                $skus = data_get($multiPageResponse, 'jingdong_sku_read_searchSkuList_responce.page.data', []);
                foreach ($skus as $sku) {
                    $wareSkus[$sku['wareId']][$sku['skuId']] = $sku;
                }
            }
        }

        return $wareSkus;
    }
}
