<?php


namespace App\Services;


use App\Services\Adaptor\Jingdong\Jobs\ItemDownloadJob;
use App\Services\Hub\Adidas\Request\Transformer\BaseTransformer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use League\Csv\Writer;

class JingdongCommentCsvExport extends TaobaoCommentCsvExport
{
    public $titles;
    public $chunkSize = 1000;

    const STORE_MAP = [
        '62710' => '8100903',
        '111445' => '8100904',
    ];

    public function exportByDate($exportDate)
    {
        $this->startAt = Carbon::now();

        $fields = [
            'jingdong_comment.origin_content', 'jingdong_comment.vender_id', 'jingdong_comment.order_id', 'sys_std_trade_item.sku_id', 'sys_std_trade_item.outer_iid', 'sys_std_platform_sku.size'
            , 'sys_std_trade_item.num', 'sys_std_trade_item.price', 'sys_std_trade_item.outer_sku_id', 'sys_std_trade.created'
            , 'adidas_items.item_id', 'sys_std_trade_item.num_iid', 'sys_std_trade.shop_code',
        ];
        $date = Carbon::parse($exportDate);
        $where[] = ['jingdong_comment.creation_time', '>=', $date->toDateString()];
        $where[] = ['jingdong_comment.creation_time', '<', $date->addDay()->toDateString()];
        $query = \DB::table('jingdong_comment')->leftJoin('sys_std_trade_item', function ($join) {
            $join->on('jingdong_comment.order_id', '=', 'sys_std_trade_item.tid')
                ->on('jingdong_comment.sku_id', '=', 'sys_std_trade_item.sku_id')
                ->where('sys_std_trade_item.platform', '=', 'jingdong');
        })->leftJoin('sys_std_trade', function ($join) {
            $join->on('jingdong_comment.order_id', '=', 'sys_std_trade.tid')
                ->where('sys_std_trade.platform', '=', 'jingdong');
        })->leftJoin('sys_std_platform_sku', function ($join) {
            $join->on('jingdong_comment.sku_id', '=', 'sys_std_platform_sku.sku_id')
                ->where('sys_std_platform_sku.platform', '=', 'jingdong');
        })->leftJoin('adidas_items', function ($join) {
            $join->on('sys_std_trade_item.outer_sku_id', '=', 'adidas_items.outer_sku_id');
        })->select($fields)->where($where)->orderBy('created');
        $titles = [
            Writer::BOM_UTF8 . 'store', 'order header', 'orderid source', 'article number', 'size', 'Recommended Retail Price', 'replyCount', 'usefulCount', 'score'
            , 'commentId', 'nickName', 'skuid', 'creationTime', 'status', 'isVenderReply'
            , 'replyId', 'skuName', 'content', 'skuImage', 'Order Created Time'
        ];

        $filePath = $this->getExportFile($exportDate);
        if (File::exists($filePath)) { // 删除重新导出
            File::delete($filePath);
            // $this->upload($exportDate);
            // return true;
        }
        File::put($filePath, '');
        $csv = Writer::createFromPath($filePath, 'w+');
        $csv->insertOne($titles);

        $query->chunk(2000, function ($data) use ($csv) {
            $formatComments = [];
            foreach ($data as $item) {
                $formatComments[] = array_values($this->formatComment($item));
            }

            $csv->insertAll($formatComments);
        });

        return $this;
    }

    public function formatComment($item)
    {
        $articleNumber = $item->item_id ?? '';
        if (empty($articleNumber) && isset($item->outer_sku_id) && Str::contains($item->outer_sku_id, ['_'])){
            $itemArr = explode('_', $item->outer_sku_id);
            $articleNumber = $itemArr[0];
        }

        $originContent = json_decode($item->origin_content, true);
        $store = self::STORE_MAP[$item->vender_id] ?? $item->vender_id;
        if (!empty($item->item_id) && empty($item->size)) {
            $params = [
                'ware_id' => $item->num_iid,
                'shop_code' => $item->shop_code,
            ];
            dispatch(new \App\Services\Adaptor\Jingdong\Jobs\ItemDownloadJob($params));
        }
        return [
            'store'                    => $store,
            'order header'             => app(BaseTransformer::class)->generatorOrderNo($originContent['orderId'], 'jingdong'),
            'orderid source'           => $originContent['orderId'],
            'article number'           => $articleNumber,
            'size'                     => $item->size,
            'Recommended Retail Price' => sprintf('%.2f', $item->price  ?? 0),
            'replyCount'               => $originContent['replyCount'],
            'usefulCount'              => $originContent['usefulCount'],
            'score'                    => $originContent['score'],
            'commentId'                => $originContent['commentId'],
            'nickName'                 => $originContent['nickName'],
            'skuid'                    => $originContent['skuid'],
            'creationTime'             => Carbon::createFromTimestampMs($originContent['creationTime'])->toDateTimeString(),
            'status'                   => $originContent['status'],
            'isVenderReply'            => $originContent['isVenderReply'],
            'replyId'                  => $originContent['replyId'],
            'skuName'                  => $originContent['skuName'],
            'content'                  => $originContent['content'] ?? '',
            'skuImage'                 => $originContent['skuImage'],
            'Order Created Time'       => (string)$item->created ?? '',
        ];
    }

    public function getFileName($fileName)
    {
        $string = Carbon::parse($fileName)->format('YmdHis');

        return 'voc_customer_review_jd_' . $string . '.csv';
    }

}
