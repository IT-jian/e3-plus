<?php


namespace App\Services\Adaptor\Jingdong\Downloader;


use App\Facades\JosClient;
use App\Models\Sys\Shop;
use App\Services\Adaptor\Contracts\DownloaderContract;
use App\Services\Adaptor\Jingdong\Repository\JingdongSkuRepository;
use App\Services\Platform\Jingdong\Client\Jos\Request\SkuReadSearchSkuListRequest;
use Illuminate\Support\Carbon;

/**
 * 平台sku下载
 *
 * Class SkuDownloader
 * @package App\Services\Adaptor\Jingdong\Downloader
 */
class SkuDownloader implements DownloaderContract
{
    private $repository;
    /**
     * @var
     */
    private $shop;

    const FIELDS = ['wareId', 'wareTitle', 'wareTitle', 'skuName', 'outerId', 'skuId', 'itemNum', 'barCode', 'status', 'created', 'modified', 'jdPrice'];
    public function __construct(JingdongSkuRepository $repository)
    {
        $this->repository = $repository;
    }

    public function download($params)
    {
        $this->shop = Shop::getShopByCode($params['shop_code']);
        $request = new SkuReadSearchSkuListRequest();
        if (isset($params['sku_id'])) {
            $request->setSkuId($params['sku_id']);
        }
        if (isset($params['ware_id'])) {
            $request->setWareId($params['ware_id']);
        }
        if (isset($params['start_created'])) {
            $request->setStartCreatedTime($params['start_created']);
        }
        if (isset($params['end_created'])) {
            $request->setEndCreatedTime($params['end_created']);
        }
        if (isset($params['start_modified'])) {
            $request->setStartModifiedTime($params['start_modified']);
        }
        if (isset($params['end_modified'])) {
            $request->setEndModifiedTime($params['end_modified']);
        }
        if (isset($params['page_no'])){ // 按照 翻页 下载
            $request->setPageNo($params['page_no']);
            $request->setPageSize($params['page_size'] ?? 50);
        }
        $fields = ['wareId', 'skuId', 'status', 'jdPrice', 'outerId', 'barCode', 'categoryId'
                   , 'skuName', 'wareTitle', 'modified', 'created'];
        $request->setField($fields);

        $response = JosClient::shop($this->shop['code'])->execute($request);
        $skus = data_get($response, 'jingdong_sku_read_searchSkuList_responce.page.data', []);

        return $this->saveSkus($skus);
    }

    public function saveSkus($skus)
    {

       $updateFields = [
           'ware_title', 'sku_name', 'outer_id', 'category_id', 'barcode'
           , 'status', 'modified', 'jd_price', 'updated_at'
       ];

        if ($skus) {
            $formatSkus = [];
            foreach ($skus as $sku) {
                $formatSkus[] = $this->formatItem($sku);
            }
            $this->repository->insertMulti($formatSkus, $updateFields);
        }

        return true;
    }

    public function formatItem($sku)
    {
        return [
            'vender_id'  => $this->shop['seller_nick'],
            'shop_code'  => $this->shop['code'],
            'ware_id'    => $sku['wareId'],
            'ware_title' => $sku['wareTitle'] ?? '',
            'sku_name'   => $sku['skuName'] ?? '',
            'outer_id'   => $sku['outerId'] ?? '0',
            'sku_id'     => $sku['skuId'],
            'barcode'    => $sku['barCode'] ?? '0',
            'category_id'=> $sku['categoryId'] ?? '0',
            'status'     => $sku['status'] ?? '0',
            'created'    => $sku['created'],
            'modified'   => $sku['modified'] ?? '',
            'jd_price'   => $sku['jdPrice'] ?? '0',
            'updated_at' => Carbon::now()->toDateTimeString(),
            'created_at' => Carbon::now()->toDateTimeString(),
        ];
    }
}
