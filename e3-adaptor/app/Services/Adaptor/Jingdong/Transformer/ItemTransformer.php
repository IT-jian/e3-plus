<?php


namespace App\Services\Adaptor\Jingdong\Transformer;


use App\Models\Sys\Shop;
use App\Models\SysStdPlatformSku;
use App\Models\JingdongItem;
use App\Services\Adaptor\Contracts\TransformerContract;
use App\Services\Adaptor\Jingdong\Repository\JingdongItemRepository;
use App\Services\Adaptor\Repository\SysStdPlatformSkuRepository;
use Exception;
use Illuminate\Support\Carbon;

class ItemTransformer implements TransformerContract
{
    const PLATFORM = 'jingdong';

    /**
     * @var string
     */
    protected $shopCode;

    /**
     * @var JingdongItemRepository
     */
    protected $itemRepository;

    public function __construct(JingdongItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    /**
     * 单个转入
     *
     * @param $params
     * @return bool
     * @throws Exception
     */
    public function transfer($params)
    {
        if (empty($params['ware_id'])) {
            throw new \InvalidArgumentException('ware_id required!');
        }
        $numIid = $params['ware_id'];
        $shopCode = $params['shop_code'] ?? '';

        $jingdongItem = JingdongItem::find($numIid);
        if (empty($jingdongItem)){
            return false;
        }
        if (empty($shopCode)) {
            $shop = Shop::select('code')->where('seller_nick', $jingdongItem['vender_id'])->first();
            $shopCode = $shop['code'];
        }
        if (empty($shopCode)) {
            throw new Exception('店铺不存在' . $jingdongItem['vender_id']);
        }
        $this->shopCode = $shopCode;
        $formatStdSkus = $this->format($jingdongItem->toArray());
        try {
            \DB::beginTransaction();
            $skus = SysStdPlatformSku::where(['platform' => self::PLATFORM, 'num_iid' => $numIid, 'shop_code' => $shopCode])->get();
            if ($skus->isEmpty()){
                $this->insert_multi('sys_std_platform_sku', $formatStdSkus);
            } else {
                $skuMap = $skus->keyBy('sku_id');
                $insertData = [];
                foreach ($formatStdSkus as $sku) {
                    if (isset($skuMap[$sku['sku_id']])) {
                        $existSku = $skuMap[$sku['sku_id']];
                        if (strtotime($existSku['modified']) < strtotime($sku['modified'])) {
                            unset($sku['created_at']);
                            $existSku->fill($sku)->save();
                        }
                        unset($skuMap[$sku['sku_id']]);
                    } else {
                        $insertData[] = $sku;
                    }
                }
                if (!empty($insertData)) {
                    $this->insert_multi('sys_std_platform_sku', $insertData);
                }
                if (!$skuMap->isEmpty()) {
                    // 已删除数据
                    $deleteSkuIds = $skuMap->keys();
                    SysStdPlatformSku::where(['platform' => self::PLATFORM, 'num_iid' => $numIid, 'shop_code' => $shopCode])->whereIn('sku_id', $deleteSkuIds)->update(['is_delete' => 1]);
                }
            }

            $this->itemRepository->updateSyncStatus([$numIid], 1);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            \Log::info('update std platform sku fail' . $e->getMessage());

            $this->itemRepository->updateSyncStatus([$numIid], 2);
            return false;
        }

        return true;
    }

    /**
     * 格式化数据
     *
     * @param $jingdongItem
     * @return array
     */
    public function format($jingdongItem)
    {
        $originContent = data_get($jingdongItem, 'origin_content');
        if (!is_array($originContent)) {
            $originItem = json_decode($originContent, true);
        } else {
            $originItem = $originContent;
        }
        $skus = $this->formatSkus($originItem);
        unset($originContent, $originItem);

        return $skus;
    }

    /**
     * 格式化 skus
     *
     * @param $item
     * @return array
     *
     * @author linqihai
     * @since 2019/12/19 21:03
     */
    public function formatSkus($item)
    {
        $skus = data_get($item, 'skus', []);

        $now = Carbon::now()->toDateTimeString();
        $formatItems = [];
        foreach ($skus as $sku) {
            $isDeleted = '-1' == $item['wareStatus'] ? 1 : ('4' == $sku['status'] ? 1 : 0);
            $attr = $this->parseSkuSizeColor($sku);
            $format = [
                'platform'       => self::PLATFORM,
                'shop_code'      => $this->shopCode,
                'num_iid'        => $item['wareId'],
                'outer_iid'      => $item['outerId'] ?? '',
                'sku_id'         => $sku['skuId'],
                'outer_id'       => $sku['outerId'] ?? '',
                'quantity'       => $sku['stockNum'] ?? 0,
                'title'          => $sku['skuName'] ?? '',
                'barcode'        => $sku['barCode'] ?? '',
                'color'          => $attr['color'],
                'size'           => $attr['size'],
                'price'          => $sku['jdPrice'] ?? '',
                'approve_status' => 1 == $sku['status'] ? 'onsale' : 'instock', // instock 在库，onsale 在售
                // 'is_delete'      => '-1' == $item['wareStatus'] ? 1 : 0, // 已删除状态
                'is_delete'      => $isDeleted, // 已删除状态
                'created'        => Carbon::createFromTimestampMs($item['created'])->toDateTimeString(),
                'modified'       => Carbon::createFromTimestampMs($item['modified'])->toDateTimeString(),
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
            $formatItems[] = $format;
        }

        return $formatItems;
    }

    /**
     * 解析尺码
     * @param $sku
     * @return mixed|string
     */
    public function parseSkuSizeColor($sku)
    {
        $color = $size = '';
        if (!isset($sku['saleAttrs'])) {
            return compact('color', 'size');
        }

        foreach ($sku['saleAttrs'] as $saleAttr) {
            if (1 == $saleAttr['index']) {
                $color = data_get($saleAttr, 'attrValueAlias.0', '');
            }
            if (2 == $saleAttr['index']) {
                $size = data_get($saleAttr, 'attrValueAlias.0', '');
            }
        }

        return compact('color', 'size');
    }

    public function insertMulti()
    {

    }

    private function insert_multi($table, $row_arr)
    {
        $row_arr = array_values($row_arr);

        $sql_mx = '';
        $key_arr = array_keys($row_arr[0]);

        foreach ($row_arr as $row) {
            $sql_mx .= ",(";
            foreach($key_arr as $key){
                if(is_null($row[$key])){
                    $sql_mx .= "NULL,";
                }else{
                    $sql_mx .= "'".addslashes($row[$key])."',";
                }
            }
            $sql_mx = rtrim($sql_mx, ','). ')';
        }
        $sql_mx = substr($sql_mx, 1);

        $query = 'INSERT INTO '.$table.'(`'.implode('`,`', $key_arr).'`) VALUES'.$sql_mx;
        return \DB::insert($query);
    }
}
