<?php


namespace App\Services\Adaptor\Jingdong\Downloader;


use App\Facades\Adaptor;
use App\Facades\JosClient;
use App\Models\Sys\Shop;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Contracts\DownloaderContract;
use App\Services\Adaptor\Jingdong\Jobs\JingdongItemTransferJob;
use App\Services\Adaptor\Jingdong\Repository\JingdongItemRepository;
use App\Services\Platform\Jingdong\Client\Jos\Request\SkuReadSearchSkuListRequest;
use App\Services\Platform\Jingdong\Client\Jos\Request\WareReadFindWareByIdRequest;
use Illuminate\Support\Carbon;

class ItemDownloader implements DownloaderContract
{
    protected $repository;
    /**
     * @var
     */
    protected $shop;

    const FIELDS = ['wareId', 'wareTitle', 'wareTitle', 'skuName', 'outerId', 'skuId', 'itemNum', 'barCode', 'status'
                    , 'created', 'modified', 'jdPrice', 'stockNum', 'saleAttrs'];

    const WARE_FIELDS = [
        'wareId', 'title', 'categoryId', 'brandId', 'wareStatus', 'outerId', 'barCode', 'created', 'modified', 'offlineTime',
        'logo', 'marketPrice', 'costPrice', 'jdPrice', 'brandName', 'shopId',
    ];
    public function __construct(JingdongItemRepository $repository)
    {
        $this->repository = $repository;
    }

    public function download($params)
    {
        if (empty($params['ware_id'])) {
            throw new \Exception('param ware_id not found');
        }

        $this->shop = Shop::getShopByCode($params['shop_code']);
        $request = new WareReadFindWareByIdRequest();
        $request->setWareId($params['ware_id']);
        $request->setField(self::WARE_FIELDS);
        $result = JosClient::shop($this->shop['code'])->execute($request);

        $validWare = data_get($result, 'jingdong_ware_read_findWareById_responce.ware', []);

        $wareSkuMap = $this->getByWareIds([$validWare['wareId']]);
        $validWare['skus'] = isset($wareSkuMap[$validWare['wareId']]) ? array_values($wareSkuMap[$validWare['wareId']]) : [];

        $this->saveItems([$validWare]);
        try {
            Adaptor::platform('jingdong')->transfer(AdaptorTypeEnum::ITEM, $params);
        } catch (\Exception $e) {
            dispatch(new JingdongItemTransferJob($params));
        }
        return true;
    }

    protected function getByWareIds($wareIds)
    {
        $requests = $wareSkus = $multiPageRequests = [];
        // status 1:上架 2:下架 4:删除
        $fields = self::FIELDS;
        $pageSize = 100;
        foreach (array_chunk($wareIds, 10) as $key => $chunk) {
            $request = new SkuReadSearchSkuListRequest();
            $request->setWareId($chunk);
            $request->setField($fields);
            $request->setPageNo('1');
            $request->setPageSize($pageSize);
            $requests[$key] = $request;
        }
        $responses = JosClient::shop($this->shop['code'])->execute($requests);
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
            $multiPageResponses = JosClient::shop($this->shop['code'])->execute($multiPageRequests);
            foreach ($multiPageResponses as $multiPageResponse) {
                $skus = data_get($multiPageResponse, 'jingdong_sku_read_searchSkuList_responce.page.data', []);
                foreach ($skus as $sku) {
                    $wareSkus[$sku['wareId']][$sku['skuId']] = $sku;
                }
            }
        }

        return $wareSkus;
    }

    public function saveItems($items)
    {

       $updateFields = [
           'ware_status', 'origin_content', 'origin_modified', 'sync_status', 'updated_at'
       ];

        if ($items) {
            $formatItems = [];
            foreach ($items as $item) {
                $formatItems[] = $this->format($item);
            }
            $this->repository->insertMulti($formatItems, $updateFields);
        }

        return true;
    }

    public function format($item)
    {
        return [
            'ware_id'         => $item['wareId'],
            'ware_status'     => $item['wareStatus'],
            'vender_id'       => $this->shop['seller_nick'],
            'origin_content'  => json_encode($item),
            'origin_created'  => Carbon::createFromTimestampMs($item['created'])->timestamp,
            'origin_modified' => Carbon::createFromTimestampMs($item['modified'])->timestamp,
            'sync_status'     => 0, // 未转入
            'created_at'      => Carbon::now()->toDateTimeString(),
            'updated_at'      => Carbon::now()->toDateTimeString(),
        ];
    }
}
