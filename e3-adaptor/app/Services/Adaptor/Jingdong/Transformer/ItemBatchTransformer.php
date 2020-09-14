<?php


namespace App\Services\Adaptor\Jingdong\Transformer;


use App\Models\SysStdPlatformSku;
use App\Services\Adaptor\Contracts\TransformerContract;
use App\Services\Adaptor\Repository\SysStdPlatformSkuRepository;
use Exception;
use InvalidArgumentException;

class ItemBatchTransformer extends ItemTransformer implements TransformerContract
{
    /**
     * 批量转入
     *
     * @param $params
     * @return bool
     * @throws Exception
     */
    public function transfer($params)
    {
        if (empty($params['ware_ids'])) {
            throw new InvalidArgumentException('ware_ids required!');
        }

        if (empty($params['shop_code'])) {
            throw new InvalidArgumentException('shop_code required!');
        }

        $where = [];
        $shopCode = $params['shop_code'];
        $where[] = ['ware_id', 'IN', $params['ware_ids']];
        $where[] = ['sync_status', 0];
        $jingdongItems = $this->itemRepository->getAll($where);
        if (empty($jingdongItems)) {
            throw new InvalidArgumentException('Jingdong Items No Found!');
        }
        $this->shopCode = $shopCode;

        $insertItems = $notOurItems = [];
        $deleteWareIds = [];
        foreach ($jingdongItems as $jingdongItem) {
            if ('-1' == $jingdongItem->ware_status) { // 平台已删除，标识为已删除
                $deleteWareIds[] = $jingdongItem->ware_id;
                continue;
            }
            $insertItems[$jingdongItem->ware_id] = $this->format($jingdongItem);
        }

        $existItems = SysStdPlatformSku::where(['platform' => self::PLATFORM])->whereIn('num_iid', $params['ware_ids'])->get();
        if (!$existItems->isEmpty()) {
            $existItems = $existItems->groupBy('num_iid');
        }

        // 批量或更新
        $deleteSkuIds = [];
        if (!empty($insertItems)) {
            $skus = $wareIds = [];
            $updateFields = ['quantity', 'title', 'color', 'size', 'price', 'approve_status', 'modified', 'updated_at', 'is_delete'];
            foreach ($insertItems as $wareId => $insertItem) {
                foreach ($insertItem as $insertSku) {
                    $skus[] = $insertSku;
                }
                $wareIds[] = $wareId;
                // 标识删除
                if (isset($existItems[$wareId])) {
                    $diffSkuIds = $this->markSkuAsDeleted($existItems[$wareId], $insertItem);
                    if ($diffSkuIds) {
                        $deleteSkuIds = array_merge($deleteSkuIds, $diffSkuIds);
                    }
                }
            }
            // 批量插入和更新
            $result = (new SysStdPlatformSkuRepository())->insertMulti($skus, $updateFields);

            $this->itemRepository->updateSyncStatus($wareIds, $result ? 1 : 2);
        }

        // 标识 item 为已删除
        if ($deleteWareIds) {
            $result = SysStdPlatformSku::where('platform', self::PLATFORM)
                ->whereIn('num_iid', array_unique($deleteWareIds))
                ->where('is_delete', 0)
                ->update(['is_delete' => 1]);
            $this->itemRepository->updateSyncStatus($deleteWareIds, $result ? 1 : 2);
        }
        // 标识部分sku为已删除
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
