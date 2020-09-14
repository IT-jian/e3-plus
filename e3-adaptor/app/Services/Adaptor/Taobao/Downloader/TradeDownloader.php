<?php


namespace App\Services\Adaptor\Taobao\Downloader;


use App\Services\Adaptor\Contracts\DownloaderContract;
use App\Services\Adaptor\Taobao\Repository\Rds\TaobaoRdsTradeRepository;
use App\Services\Adaptor\Taobao\Repository\TaobaoTradeRepository;
use Carbon\Carbon;

class TradeDownloader implements DownloaderContract
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
     * @param $tid
     *
     * @author linqihai
     * @since 2019/12/19 9:54
     */
    public function download($where)
    {
        if (isset($where['tids']) && !empty($where['tids'])) {
            $where[] = ['tid', 'in', $where['tids']];
            unset($where['tids']);
        }
        $formatData = [];
        $fields = ['tid','seller_nick','status','type','jdp_response','jdp_created','jdp_modified', 'created', 'modified'];
        $trades = $this->rds->getAll($where, $fields, 'jdp_modified asc,tid asc');
        if ($trades->isEmpty()) {
            throw new \Exception('RDS 订单不存在，下载失败！');
        }
        $where = [];
        $where[] = ['tid', 'in', $trades->pluck('tid')->toArray()];
        $existTrades = $this->trade->getAll($where, ['tid', 'origin_modified']);
        if (!$existTrades->isEmpty()) {
            $existTrades = $existTrades->keyBy('tid');
        }
        $updateTrades = $insertTrades = [];
        foreach ($trades as $rdsTrade) {
            // update check exist
            if (!$existTrades->isEmpty() && isset($existTrades[$rdsTrade->tid])) {
                if ($existTrades[$rdsTrade->tid]->origin_modified >= strtotime($rdsTrade->jdp_modified)) {
                    // continue;
                }
                $updateTrades[$rdsTrade->tid] = $existTrades[$rdsTrade->tid];
            } else {
                $insertTrades[] = $rdsTrade->tid;
            }
            $formatData[$rdsTrade->tid] = $this->format($rdsTrade);
        }
        if ($formatData) {
            $updateFields = ['status', 'type', 'origin_content', 'origin_modified', 'sync_status', 'updated_at', 'modified', 'created'];
            $this->trade->insertMulti($formatData, $updateFields);
            /*foreach ($formatData as $tid => $trade) {
                dispatch(new TradeDownloadFinishJob(['tid' => $tid, 'platform' => 'taobao']));
            }*/
        }
    }

    public function format($trade)
    {
        return [
            'tid'             => $trade->tid,
            'seller_nick'     => $trade->seller_nick,
            'status'          => $trade->status,
            'type'            => $trade->type,
            'origin_content'  => $trade->jdp_response,
            'origin_created'  => strtotime($trade->jdp_created),
            'origin_modified' => strtotime($trade->jdp_modified),
            'created'         => $trade->created,
            'modified'        => $trade->modified,
            'sync_status'     => 0, // 未转入
            'created_at'      => Carbon::now()->toDateTimeString(),
            'updated_at'      => Carbon::now()->toDateTimeString(),
        ];
    }

    public function downByTimeRange()
    {

    }
}
