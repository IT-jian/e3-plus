<?php


namespace App\Services\Adaptor\Taobao\Transformer;


use App\Models\Sys\Shop;
use App\Models\SysStdPlatformSku;
use App\Services\Adaptor\Contracts\TransformerContract;
use App\Services\Adaptor\Repository\SysStdPlatformSkuRepository;
use Exception;
use InvalidArgumentException;

class ItemBatchTransformer extends ItemTransformer implements TransformerContract
{
    /**
     * 单个转入
     *
     * @param $params
     * @return bool
     * @throws Exception
     */
    public function transfer($params)
    {
        if (empty($params['num_iids'])) {
            throw new InvalidArgumentException('num_iids required!');
        }
        $where = [];
        $numIids = $params['num_iids'];
        $where[] = ['num_iid', 'IN', $numIids];
        $taobaoItems = $this->itemRepository->getAll($where);
        if (empty($taobaoItems)) {
            throw new InvalidArgumentException('RDS Item数据不存在!');
        }

        $sellerNickShopCodeMap = [];
        $shops = Shop::available(self::PLATFORM)->get();
        foreach ($shops as $shop) {
            $sellerNickShopCodeMap[$shop['seller_nick']] = $shop['code'];
        }
        if (empty($sellerNickShopCodeMap)) {
            throw new Exception('店铺不存在，请添加店铺后执行');
        }

        $existItems = SysStdPlatformSku::where(['platform' => self::PLATFORM])->whereIn('num_iid', $numIids)->get();
        if (!$existItems->isEmpty()) {
            $existItems = $existItems->groupBy('num_iid');
        }

        $updateItems = $insertItems = $notOurItems = [];
        foreach ($taobaoItems as $taobaoItem) {
            $this->shopCode = $sellerNickShopCodeMap[$taobaoItem->seller_nick] ?? null;
            if (empty($this->shopCode)) {
                $notOurItems[] = $taobaoItem->num_iid;
                continue;
            }
            $insertItems[$taobaoItem->num_iid] = $this->format($taobaoItem);
        }

        // 批量或更新
        $deleteSkuIds = [];
        if (!empty($insertItems)) {
            $skus = $numIids = [];
            $updateFields = ['outer_id', 'outer_iid', 'barcode', 'quantity', 'title', 'color', 'size', 'price', 'approve_status', 'modified', 'updated_at', 'is_delete'];
            foreach ($insertItems as $numIid => $insertItem) {
                foreach ($insertItem['skus'] as $insertSku) {
                    $skus[] = $insertSku;
                }
                $numIids[] = $numIid;
                // 标识删除
                if (isset($existItems[$numIid])) {
                    $diffSkuIds = $this->markSkuAsDeleted($existItems[$numIid], $insertItem['skus']);
                    if ($diffSkuIds) {
                        $deleteSkuIds = array_merge($deleteSkuIds, $diffSkuIds);
                    }
                }
            }
            // 批量插入和更新
            $result = (new SysStdPlatformSkuRepository())->insertMulti($skus, $updateFields);

            $this->itemRepository->updateSyncStatus($numIids, $result ? 1 : 2);
        }
        // 更新为非本系统订单
        if (!empty($notOurItems)) {
            $this->itemRepository->updateSyncStatus($notOurItems, 3);
        }
        if ($deleteSkuIds) {
            SysStdPlatformSku::where('platform', self::PLATFORM)
                ->whereIn('sku_id', array_unique($deleteSkuIds))
                ->where('is_delete', 0)
                ->update(['is_delete' => 1]);
        }
        return true;
    }

    /**
     * 标识为删除的sku
     *
     * @param $existSkus
     * @param $newSkus
     * @return array
     */
    public function markSkuAsDeleted($existSkus, $newSkus)
    {
        $existSkuIds = [];
        foreach ($existSkus as $existSku) {
            if (0 == $existSku['is_delete']) {
                $existSkuIds[] = $existSku['sku_id'];
            }
        }
        $newSkuIds = [];
        foreach ($newSkus as $newSku) {
            $newSkuIds[] = $newSku['sku_id'];
        }
        $diffSkuIds = array_diff($existSkuIds, $newSkuIds);

        return $diffSkuIds;
    }

}
