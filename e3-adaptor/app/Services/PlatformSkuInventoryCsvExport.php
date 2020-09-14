<?php


namespace App\Services;


use App\Jobs\DingTalkNoticeTextSendJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use League\Csv\ByteSequence;
use League\Csv\Writer;
use Storage;

/**
 * 平台sku库存CSV下载
 *
 * Class PlatformSkuInventoryCsvExport
 * @package App\Services
 */
class PlatformSkuInventoryCsvExport
{
    public $titles;
    public $chunkSize = 1000;
    private $shopCode;

    /**
     * @var Carbon
     */
    private $startAt;

    public function exportByDate($exportDate, $shopCode = '')
    {
        $this->startAt = Carbon::now();
        $this->shopCode = $shopCode;
        $fields = [
            'sys_std_platform_sku.num_iid',
            'sys_std_platform_sku.sku_id',
            'sys_std_platform_sku.outer_id',
            'sys_std_platform_sku.quantity',
            'sys_std_platform_sku.size',
            'sys_std_platform_sku.barcode',
            'sys_std_platform_sku.platform',
            'sys_std_platform_sku.shop_code',
            'adidas_items.item_id',
        ];

        $titles = [
            ByteSequence::BOM_UTF8 . '商品ID',
            ByteSequence::BOM_UTF8 . '可售库存',
            ByteSequence::BOM_UTF8 . 'article no',
            ByteSequence::BOM_UTF8 . 'size no',
            ByteSequence::BOM_UTF8 . 'barcode',
            ByteSequence::BOM_UTF8 . '店铺',
            ByteSequence::BOM_UTF8 . 'Enterprise code',
        ];

        $filePath = $this->getExportFile($exportDate);
        if (File::exists($filePath)) {
            File::delete($filePath);
            /*$this->upload($exportDate);

            return true;*/
        }
        File::put($filePath, '');
        $csv = Writer::createFromPath($filePath, 'w+');
        $csv->insertOne($titles);
        $skuId = 0;

        do {
            $where = [];
            if ($shopCode) {
                $where['shop_code'] = $shopCode;
            }
            $where['is_delete'] = 0;// 未删除
            if ($skuId) {
                $where[] = ['sku_id', '>', $skuId];
            }
            $query = \DB::table('sys_std_platform_sku')
                ->leftJoin('adidas_items', function ($join) {
                    $join->on('sys_std_platform_sku.outer_id', '=', 'adidas_items.outer_sku_id');
                })
                ->select($fields)->where($where)->orderBy('sys_std_platform_sku.sku_id');
            $items = $query->limit(10000)->get();
            if ($items->isEmpty()) {
                break;
            }
            $formatSkus = [];
            foreach ($items as $sku) {
                $formatSku = $this->formatSku($sku);
                $formatSkus[] = array_values($formatSku);
            }
            $csv->insertAll($formatSkus);

            $lastItem = $items->pop();
            $skuId = $lastItem->sku_id;
        } while (true);
        return $this;
    }

    public function formatSku($sku)
    {
        $articleNumber = $sku->item_id ?? '';
        if (empty($articleNumber) && isset($sku->outer_id) && Str::contains($sku->outer_id, ['_'])){
            $itemArr = explode('_', $sku->outer_id);
            $articleNumber = $itemArr[0];
        }

        return [
            'num_iid' => $sku->num_iid,
            'quantity' => $sku->quantity,
            'articleNo' => $articleNumber,
            'size' => $sku->size,
            'barcode' => $sku->outer_id,
            'platform' => $this->platformColumnMap($sku->platform),
            'shop_code' => $sku->shop_code,
        ];
    }

    public function platformColumnMap($platform)
    {
        return $platform == 'taobao' ? 'TM' : 'JD';
    }

    public function getExportFile($fileName)
    {
        $fileName = $this->getFileName($fileName);

        if (!File::isDirectory(storage_path('adaptor_export/'))) {
            File::makeDirectory(storage_path('adaptor_export/'));
        }

        return storage_path('adaptor_export/') . $fileName;
    }

    public function getFileName($fileName)
    {
        $map = [
            'EMC1' => 'TMFS',
            'EMC2' => 'TMKD',
            'EMC3' => 'TMFT',
            'EMC5' => 'JDFS',
            'EMC6' => 'JDKD',
        ];

        if ($this->shopCode) {
            $prefix = $map[$this->shopCode] ?? $this->shopCode;
        } else {
            $prefix = $this->platformColumnMap(config('adaptor.default'));
        }

        $string = Carbon::parse($fileName)->format('YmdHis');

        return $prefix . '_' . $string . '_inventory_snap.csv';
    }

    public function upload($file, $toPath = '/ODP/platform_inventory_cn/', $disk = 'inventory')
    {
        $uploadStartAt = Carbon::now();
        $result = Storage::disk($disk)->putFileAs($toPath, $this->getExportFile($file), $this->getFileName($file), 'public');
        if ($result) {
            File::delete($this->getExportFile($file));
        }
        $endAt = Carbon::now();
        $diff = $uploadStartAt->timestamp - $this->startAt->timestamp;
        $diffUpload = $endAt->timestamp - $uploadStartAt->timestamp;
        $prefixMessage = sprintf(" | 开始时间导出：%s | 开始上传时间：%s | 结束上传时间：%s | 导出耗时：%s s | 上传耗时：%s s",
                                 $this->startAt->toDateTimeString(), $uploadStartAt->toDateTimeString(), $endAt->toDateTimeString(), $diff, $diffUpload);
        $params['message'] = !$result ? '库存上传失败：' . $this->getFileName($file) : '库存上传成功：' . $result;
        $params['message'] .= $prefixMessage;
        dispatch(new DingTalkNoticeTextSendJob($params));
    }
}
