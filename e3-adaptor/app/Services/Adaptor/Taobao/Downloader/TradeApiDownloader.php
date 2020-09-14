<?php


namespace App\Services\Adaptor\Taobao\Downloader;


use App\Models\Sys\Shop;
use App\Services\Adaptor\Contracts\DownloaderContract;
use App\Services\Adaptor\Taobao\Api\TradeApi;
use App\Services\Adaptor\Taobao\Repository\Rds\TaobaoRdsTradeRepository;
use App\Services\Adaptor\Taobao\Repository\TaobaoTradeRepository;
use Carbon\Carbon;

class TradeApiDownloader implements DownloaderContract
{
    /**
     * @var TaobaoTradeRepository
     */
    private $trade;

    /**
     * @var TaobaoRdsTradeRepository
     */
    private $rds;

    public function __construct(TaobaoRdsTradeRepository $rds, TaobaoTradeRepository $trade)
    {
        $this->rds = $rds;
        $this->trade = $trade;
    }

    /**
     * 根据交易号下载
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception $e
     *
     * @author linqihai
     * @since 2019/12/19 9:54
     */
    public function download($params)
    {
        $formatData = [];
        $shopCode = $params['shopCode'] ?? '';
        if (empty($shopCode)) {
            throw new \Exception('API下载需要指定店铺，下载失败！');
        }
        $tids = $params['tids'];
        $where = [];
        $where[] = ['tid', 'in', $tids];
        $existTrades = $this->trade->getAll($where, ['tid', 'origin_modified']);
        if (!$existTrades->isEmpty()) {
            $tids = array_diff($tids, $existTrades->pluck('tid')->toArray());
        }
        if (empty($tids)) {
            return true;
        }
        $trades = $this->queryFromApi($tids, $shopCode);
        foreach ($trades as $rdsTrade) {
            $formatData[$rdsTrade['tid']] = $this->format($rdsTrade);
        }
        if ($formatData) {
            $updateFields = ['status', 'type', 'origin_content', 'sync_status', 'updated_at', 'modified', 'created'];
            $this->trade->insertMulti($formatData, $updateFields);
        }

        return true;
    }

    public function format($trade)
    {
        return [
            'tid'             => $trade['tid'],
            'seller_nick'     => $trade['seller_nick'],
            'status'          => $trade['status'],
            'type'            => $trade['type'],
            'origin_content'  => $trade['jdp_response'],
            'origin_created'  => strtotime($trade['jdp_created']),
            'origin_modified' => strtotime($trade['jdp_modified']),
            'created'         => $trade['created'],
            'modified'        => $trade['modified'],
            'created_at'      => Carbon::now()->toDateTimeString(),
            'updated_at'      => Carbon::now()->toDateTimeString(),
            'sync_status'     => 0, // 未转入
        ];
    }

    public function queryFromApi($tids, $shopCode)
    {
        $formatTrades = [];
        $shop = Shop::getShopByCode($shopCode);
        foreach (array_chunk($tids, 50) as $chunkTid) {
            $trades = (new TradeApi($shop))->find($chunkTid);
            foreach ($trades as $originTrade) {
                $trade = data_get($originTrade, 'trade_fullinfo_get_response.trade', []);
                if (empty($trade)) {
                    continue;
                }
                $formatTrades[] = [
                    'tid' => $trade['tid'] ?? '',
                    'seller_nick' => $trade['seller_nick'] ?? '',
                    'status' => $trade['status'],
                    'type' => $trade['type'],
                    'jdp_response' => json_encode($originTrade),
                    'jdp_created' => $trade['created'] ?? Carbon::now()->toDateTimeString(),
                    'jdp_modified' => $trade['modified'] ?? Carbon::now()->toDateTimeString(),
                    'created'         => $trade['created'],
                    'modified'        => $trade['modified'],
                ];
            }
        }

        return $formatTrades;
    }
}
