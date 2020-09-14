<?php


namespace App\Services;


use App\Jobs\DingTalkNoticeTextSendJob;
use App\Services\Hub\Adidas\Request\Transformer\BaseTransformer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use League\Csv\Writer;
use Storage;

class TaobaoCommentCsvExport
{
    public $titles;
    public $chunkSize = 1000;

    /**
     * @var Carbon
     */
    protected $startAt;

    const STORE_MAP = [
        'adidas官方旗舰店' => '8100901',
        'adidas儿童官方旗舰店' => '8100908',
        'adidas足球旗舰店' => '8100906',
    ];

    public function exportByDate($exportDate)
    {
        $this->startAt = Carbon::now();

        $fields = [
            'taobao_comment.origin_content', 'taobao_comment.tid', 'taobao_comment.seller_nick', 'sys_std_trade_item.outer_iid', 'sys_std_trade_item.color', 'sys_std_trade_item.size'
            , 'sys_std_trade_item.num', 'sys_std_trade_item.price', 'sys_std_trade_item.payment', 'sys_std_trade_item.row_index', 'sys_std_trade_item.outer_sku_id', 'sys_std_trade.created'
            , 'adidas_items.item_id'
        ];
        $date = Carbon::parse($exportDate);
        $where[] = ['taobao_comment.created', '>=', $date->toDateString()];
        $where[] = ['taobao_comment.created', '<', $date->addDay()->toDateString()];
        $query = \DB::table('taobao_comment')->leftJoin('sys_std_trade_item', function ($join) {
            $join->on('taobao_comment.tid', '=', 'sys_std_trade_item.tid')
                ->on('taobao_comment.oid', '=', 'sys_std_trade_item.oid')
                ->on('taobao_comment.num_iid', '=', 'sys_std_trade_item.num_iid')
                ->where('sys_std_trade_item.platform', '=', 'taobao');
        })->leftJoin('sys_std_trade', function ($join) {
            $join->on('taobao_comment.tid', '=', 'sys_std_trade.tid')
                ->where('sys_std_trade.platform', '=', 'taobao');
        })->leftJoin('adidas_items', function ($join) {
            $join->on('sys_std_trade_item.outer_sku_id', '=', 'adidas_items.outer_sku_id');
        })->select($fields)->where($where)->orderBy('created');
        $titles = [
            Writer::BOM_UTF8 . 'store', 'order header', 'order item', 'article number', 'size', 'quantity', 'Recommended Retail Price'
            , 'review content', 'review created time', 'item price', 'payment', 'item title', 'customer nick'
            , 'number iid', 'oid', 'oid string', 'review receiver', 'review result', "reviewer's role", 'tid', 'tid string', 'Order Created Time'
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
        $originContent = json_decode($item->origin_content, true);
        $store = self::STORE_MAP[$item->seller_nick] ?? $item->seller_nick;
        $articleNumber = $item->item_id ?? '';
        if (empty($articleNumber) && isset($item->outer_sku_id) && Str::contains($item->outer_sku_id, ['_'])){
            $itemArr = explode('_', $item->outer_sku_id);
            $articleNumber = $itemArr[0];
        }
        return [
            'store'                    => $store,
            'order header'             => app(BaseTransformer::class)->generatorOrderNo($item->tid, 'taobao'),
            'order item'               => $item->row_index ?? 0,
            'article number'           => $articleNumber,
            'size'                     => $item->size ?? '',
            'quantity'                 => $item->num ?? 0,
            'Recommended Retail Price' => sprintf('%.2f', $item->price ?? 0),
            'review content'           => $originContent['content'] ?? '',
            'review created time'      => $originContent['created'] ?? '',
            'item price'               => sprintf('%.2f', $originContent['item_price'] ?? 0),
            'payment'                  => sprintf('%.2f', $item->payment ?? 0),
            'item title'               => $originContent['item_title'] ?? '',
            'customer nick'            => $originContent['nick'] ?? '',
            'number iid'               => $originContent['number_iid'] ?? '',
            'oid'                      => $originContent['oid'] ?? '',
            'oid string'               => (string)$originContent['oid'] ?? '',
            'review receiver'          => $originContent['rated_nick'] ?? '',
            'review result'            => $originContent['result'] ?? '',
            "reviewer's role"          => $originContent['role'] ?? '',
            'tid'                      => $item->tid ?? '',
            'tid string'               => (string)$item->tid ?? '',
            'Order Created Time'       => (string)$item->created ?? '',
        ];
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
        $string = Carbon::parse($fileName)->format('YmdHis');

        return 'voc_customer_review_tb_' . $string . '.csv';
    }

    public function upload($file, $toPath = '/', $disk = 'sftp')
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
        $params['message'] = !$result ? '评论上传失败：' . $this->getFileName($file) : '评论上传成功：' . $result;
        $params['message'] .= $prefixMessage;
        dispatch(new DingTalkNoticeTextSendJob($params));
    }
}
