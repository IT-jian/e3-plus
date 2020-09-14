<?php


namespace App\Services\Adaptor\Taobao\Transformer;


use App\Models\Sys\Shop;
use App\Models\SysStdPlatformSku;
use App\Models\TaobaoItem;
use App\Services\Adaptor\Contracts\TransformerContract;
use App\Services\Adaptor\Taobao\Repository\TaobaoItemRepository;
use App\Services\Adaptor\Taobao\Repository\TaobaoRefundRepository;
use Exception;
use Illuminate\Support\Carbon;

class ItemTransformer implements TransformerContract
{
    const PLATFORM = 'taobao';

    /**
     * @var string
     */
    protected $shopCode;

    /**
     * @var TaobaoItemRepository
     */
    protected $itemRepository;

    public function __construct(TaobaoItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    /**
     * 单个转入
     *
     * @param $numIid
     * @param string $shopCode
     * @return bool
     *
     * @author linqihai
     * @since 2019/12/19 21:22
     */
    public function transfer($params)
    {
        if (empty($params['num_iid'])) {
            throw new \InvalidArgumentException('num_iid required!');
        }
        $numIid = $params['num_iid'];
        $shopCode = $params['shop_code'] ?? '';

        $taobaoItem = TaobaoItem::find($numIid);
        if (empty($taobaoItem)){
            return false;
        }
        if (empty($shopCode)) {
            $shop = Shop::select('code')->where('seller_nick', $taobaoItem['seller_nick'])->first();
            $shopCode = $shop['code'];
        }
        if (empty($shopCode)) {
            throw new Exception('店铺不存在' . $taobaoItem['seller_nick']);
        }
        $this->shopCode = $shopCode;
        $formatStdItems = $this->format($taobaoItem->toArray());
        $formatStdSkus = $formatStdItems['skus'];
        try {
            \DB::beginTransaction();
            // 删除，重新插入

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
     * @param $taobaoItem
     * @return array
     *
     * @author linqihai
     * @since 2019/12/19 20:48
     */
    public function format($taobaoItem)
    {
        $originContent = data_get($taobaoItem, 'origin_content');
        if (!is_array($originContent)) {
            $originItem = json_decode($originContent, true);
        } else {
            $originItem = $originContent;
        }
        $originItem = data_get($originItem, 'item_get_response.item', []);
        // 设置是否已经删除
        $originItem['is_delete'] = data_get($taobaoItem, 'origin_delete', 0);
        $skus = $this->formatSkus($originItem);

        unset($originContent, $originItem);
        return compact('skus');
    }

    /**
     * 格式化 skus
     * @param $item
     * @return array
     *
     * @author linqihai
     * @since 2019/12/19 21:03
     */
    public function formatSkus($item)
    {
        $skus = data_get($item, 'skus.sku', []);

        $now = Carbon::now()->toDateTimeString();
        $formatItems = [];
        foreach ($skus as $sku) {
            $color = $size = '';
            $propertiesName = $sku['properties_name'];
            $properties = explode(';', $propertiesName);
            if (isset($properties[0])) {
                $colorProperty = $properties[0];
                $colorArr = explode(":", $colorProperty);
                if (isset($colorArr['3'])) {
                    $color = $colorArr['3'];
                }
            }
            if (isset($properties[1])) {
                $sizeProperty = $properties[1];
                $sizeArr = explode(":", $sizeProperty);
                if (isset($sizeArr['3'])) {
                    $size = $sizeArr['3'];
                }
            }
            $format = [
                'platform'       => self::PLATFORM,
                'shop_code'      => $this->shopCode,
                'num_iid'        => $item['num_iid'],
                'outer_iid'      => $item['outer_id'] ?? '',
                'sku_id'         => $sku['sku_id'],
                'outer_id'       => $sku['outer_id'] ?? '',
                'quantity'       => $sku['quantity'] ?? 0,
                'title'          => $item['title'] ?? '',
                'barcode'        => $sku['barcode'] ?? '',
                'color'          => $color,
                'size'           => $size,
                'price'          => $sku['price'] ?? '',
                'approve_status' => $sku['approve_status'] ?? 'instock', // instock 在库，onsale 在售
                'is_delete'      => $item['is_delete'] ?? 0,
                'created'        => $sku['created'] ?? '',
                'modified'       => $sku['modified'] ?? '',
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
            $formatItems[] = $format;
        }

        return $formatItems;
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
