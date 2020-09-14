<?php


namespace App\Services\Adaptor\Jingdong\Downloader;


use App\Models\Sys\Shop;
use App\Services\Adaptor\Jingdong\Api\TradeApi;
use Carbon\Carbon;

/**
 * 通过接口批量下载订单
 *
 * Class TradeApiBatchDownloader
 * @package App\Services\Adaptor\Jingdong\Downloader
 */
class TradeApiBatchDownloader extends TradeDownloader
{
    /**
     * 根据交易号下载
     *
     * @param $params
     * @throws \Exception
     *
     * @return bool
     */
    public function download($params)
    {
        $orderIds = $params['order_ids'];
        $shopCode = $params['shop_code'];
        $shop = Shop::getShopByCode($shopCode);

        $formatData = [];
        $where = [];
        $where[] = ['order_id', 'in', $orderIds];
        $existTrades = $this->trade->getAll($where, ['order_id', 'version']);
        if ($existTrades->isNotEmpty()) { // 已存在的不再下载
            $orderIds = array_diff($orderIds, $existTrades->pluck('orde_id')->toArray());
        }
        if (empty($orderIds)) {
            return true;
        }
        $trades = (new TradeApi($shop))->findMulti($orderIds);
        if (empty($trades)) {
            throw new \Exception('jos trade api not found ');
        }
        foreach ($trades as $trade) {
            $formatData[$trade['orderId']] = $this->format($trade);
        }
        $updateFields = ['state', 'order_type', 'origin_content', 'origin_modified', 'sync_status', 'updated_at', 'modified', 'version'];
        $this->trade->insertMulti($formatData, $updateFields);

        return true;
    }

    public function tradeVersion($trade)
    {
        return strtotime($trade['modified']) * 1000;
    }

    public function format($trade)
    {
        return [
            'order_id'        => $trade['orderId'],
            'vender_id'       => $trade['venderId'],
            'state'           => $trade['orderState'],
            'order_type'      => $trade['orderType'],
            'direct_parent_order_id' => $trade['directParentOrderId'] ?? '',
            'parent_order_id' => $trade['parentOrderId'] ?? '',
            'origin_content'  => json_encode($trade),
            'origin_created'  => strtotime($trade['orderStartTime']),
            'origin_modified' => strtotime($trade['modified']),
            'created'         => $trade['orderStartTime'],
            'modified'        => $trade['modified'],
            'version'         => $this->tradeVersion($trade),
            'sync_status'     => 0, // 未转入
            'created_at'      => Carbon::now()->toDateTimeString(),
            'updated_at'      => Carbon::now()->toDateTimeString(),
        ];
    }
}
