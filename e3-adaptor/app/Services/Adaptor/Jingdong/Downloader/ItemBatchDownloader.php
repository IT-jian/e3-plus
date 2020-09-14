<?php


namespace App\Services\Adaptor\Jingdong\Downloader;


use App\Facades\Adaptor;
use App\Facades\JosClient;
use App\Models\Sys\Shop;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Contracts\DownloaderContract;
use App\Services\Adaptor\Jingdong\Jobs\JingdongItemBatchTransferJob;
use App\Services\Platform\Jingdong\Client\Jos\Request\WareReadSearchWare4RecycledRequest;
use App\Services\Platform\Jingdong\Client\Jos\Request\WareReadSearchWare4ValidRequest;

class ItemBatchDownloader extends ItemDownloader implements DownloaderContract
{
    protected $repository;
    /**
     * @var
     */
    protected $shop;

    public function download($params)
    {
        $this->shop = Shop::getShopByCode($params['shop_code']);
        $items = $this->getByPage($params);
        if (empty($items)) {
            return true;
        }
        $this->saveItems($items);

        $wareIds = [];
        foreach ($items as $item) {
            $wareIds[] = $item['wareId'];
        }
        $where = ['ware_ids' => $wareIds, 'shop_code' => $this->shop['code']];
        try {
            Adaptor::platform('jingdong')->transfer(AdaptorTypeEnum::ITEM_BATCH, $where);
        } catch (\Exception $e) {
            dispatch(new JingdongItemBatchTransferJob($where));
        }

        $this->downloadRecycled($params);

        return true;
    }

    public function getByPage($params)
    {
        $request = new WareReadSearchWare4ValidRequest();
        $request->setPageNo($params['page']);
        $request->setPageSize($params['page_size']);
        $request->setField(self::WARE_FIELDS);
        $request->setStartModifiedTime($params['start_modified']);
        $request->setEndModifiedTime($params['end_modified']);
        $result = JosClient::shop($this->shop['code'])->execute($request);

        $validWare = data_get($result, 'jingdong_ware_read_searchWare4Valid_responce.page.data', []);
        $wareIds = [];
        foreach ($validWare as $item) {
            $wareIds[] = $item['wareId'];
        }
        if (empty($wareIds)) {
            return [];
        }
        $wareSkuMap = $this->getByWareIds($wareIds);
        foreach ($validWare as $key => $ware) { // 查询sku
            $validWare[$key]['skus'] = isset($wareSkuMap[$ware['wareId']]) ? array_values($wareSkuMap[$ware['wareId']]) : [];
        }

        return $validWare;
    }

    /**
     * 下载回收站的商品
     *
     * @param $params
     * @return bool
     */
    public function downloadRecycled($params)
    {
        $items = $this->getRecycledByPage($params);
        if (empty($items)) {
            return true;
        }
        $this->saveItems($items);

        $wareIds = [];
        foreach ($items as $item) {
            $wareIds[] = $item['wareId'];
        }
        $where = ['ware_ids' => $wareIds, 'shop_code' => $this->shop['code']];
        try {
            Adaptor::platform('jingdong')->transfer(AdaptorTypeEnum::ITEM_BATCH, $where);
        } catch (\Exception $e) {
            dispatch(new JingdongItemBatchTransferJob($where));
        }

        return true;
    }

    /**
     * 获取回收站商品
     *
     * @param $params
     * @return array|mixed
     */
    public function getRecycledByPage($params)
    {
        $request = new WareReadSearchWare4RecycledRequest();
        $request->setPageNo($params['page']);
        $request->setPageSize($params['page_size']);
        $request->setField(self::WARE_FIELDS);
        $request->setStartModifiedTime($params['start_modified']);
        $request->setEndModifiedTime($params['end_modified']);
        $result = JosClient::shop($this->shop['code'])->execute($request);

        $recycledWare = data_get($result, 'jingdong_ware_read_searchWare4Recycled_responce.page.data', []);
        $wareIds = [];
        foreach ($recycledWare as $item) {
            $wareIds[] = $item['wareId'];
        }
        if (empty($wareIds)) {
            return [];
        }
        foreach ($recycledWare as $key => $ware) { // 查询sku
            $recycledWare[$key]['skus'] = [];
        }

        return $recycledWare;
    }
}
