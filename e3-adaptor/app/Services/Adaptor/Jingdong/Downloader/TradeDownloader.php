<?php


namespace App\Services\Adaptor\Jingdong\Downloader;


use App\Services\Adaptor\Contracts\DownloaderContract;
use App\Services\Adaptor\Jingdong\Repository\Rds\JingdongRdsTradeRepository;
use App\Services\Adaptor\Jingdong\Repository\JingdongTradeRepository;
use Carbon\Carbon;

class TradeDownloader implements DownloaderContract
{
    /**
     * @var JingdongTradeRepository
     */
    protected $trade;

    /**
     * @var JingdongRdsTradeRepository
     */
    protected $rds;

    public function __construct(JingdongRdsTradeRepository $rds, JingdongTradeRepository $trade)
    {
        $this->rds = $rds;
        $this->trade = $trade;
    }

    /**
     * 根据交易号下载
     *
     * @param $where
     * @throws \Exception
     */
    public function download($where)
    {
        if (isset($where['order_ids']) && !empty($where['order_ids'])) {
            $where[] = ['orderId', 'in', $where['order_ids']];
            unset($where['order_ids']);
        } else if (isset($where['order_id']) && !empty($where['order_id'])) {
            $where['orderId'] = $where['order_id'];
            unset($where['order_id']);
        }
        $formatData = [];
        $fields = [
            'orderId', 'venderId', 'state', 'orderType', 'responseJson', 'pushCreated', 'pushModified', 'created', 'modified', 'version',
        ];
        $trades = $this->rds->getAll($where, $fields, 'pushModified asc,orderId asc');
        if ($trades->isEmpty()) {
            throw new \Exception('RDS 订单不存在，下载失败！');
        }
        $where = [];
        $where[] = ['order_id', 'in', $trades->pluck('order_id')->toArray()];
        $existTrades = $this->trade->getAll($where, ['order_id', 'version']);
        if (!$existTrades->isEmpty()) {
            $existTrades = $existTrades->keyBy('order_id');
        }
        foreach ($trades as $rdsTrade) {
            // update check exist
            if (!$existTrades->isEmpty() && isset($existTrades[$rdsTrade->orderId])) {
                if ($existTrades[$rdsTrade->orderId]->version != $rdsTrade->version) {
                    // continue;
                }
            }
            $formatData[$rdsTrade->orderId] = $this->format($rdsTrade);
        }
        if ($formatData) {
            $updateFields = ['state', 'order_type', 'origin_content', 'origin_modified', 'direct_parent_order_id', 'parent_order_id', 'sync_status', 'updated_at', 'modified', 'version'];
            $this->trade->insertMulti($formatData, $updateFields);
        }
    }

    public function format($trade)
    {
        $originContent = json_decode($trade->responseJson, true);

        return [
            'order_id'        => $trade->orderId,
            'vender_id'       => $trade->venderId,
            'state'           => $trade->state,
            'order_type'      => $trade->orderType,
            'direct_parent_order_id' => $originContent['directParentOrderId'] ?? '',
            'parent_order_id' => $originContent['parentOrderId'] ?? '',
            'origin_content'  => $trade->responseJson,
            'origin_created'  => strtotime($trade->pushCreated),
            'origin_modified' => strtotime($trade->pushModified),
            'created'         => $trade->created,
            'modified'        => $trade->modified,
            'version'         => $trade->version,
            'created_at'      => Carbon::now()->toDateTimeString(),
            'updated_at'      => Carbon::now()->toDateTimeString(),
            'sync_status'     => 0, // 未转入
        ];
    }
}
