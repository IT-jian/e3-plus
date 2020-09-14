<?php


namespace App\Services\Adaptor\Jingdong\Downloader;


use App\Models\Sys\Shop;
use App\Services\Adaptor\Jingdong\Api\TradeApi;
use Carbon\Carbon;

/**
 * 通过接口下载订单数据到 jingdong_trade 表
 *
 * Class TradeApiDownloader
 * @package App\Services\Adaptor\Jingdong\Downloader
 */
class TradeApiDownloader extends TradeDownloader
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
        $orderId = $params['order_id'];
        $shopCode = $params['shop_code'];
        $shop = Shop::getShopByCode($shopCode);

        $trade = (new TradeApi($shop))->find($orderId);
        if (empty($trade)) {
            throw new \Exception('jos trade api not found ' . $orderId);
        }
        $formatData = [];
        $where = [];
        $where[] = ['order_id', $orderId];
        $existTrade = $this->trade->getRow($where, ['order_id', 'version']);
        if (!empty($existTrade)) {
            if ($existTrade->version >= $this->tradeVersion($trade)) {
                return true;
            }
        }
        $formatData[$trade['orderId']] = $this->format($trade);
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
            'created_at'      => Carbon::now()->toDateTimeString(),
            'updated_at'      => Carbon::now()->toDateTimeString(),
            'sync_status'     => 0, // 未转入
        ];
    }
}
