<?php


namespace App\Services\Adaptor\Taobao\Downloader;


use App\Models\Sys\Shop;
use App\Services\Adaptor\Contracts\DownloaderContract;
use App\Services\Adaptor\Taobao\Repository\Rds\TaobaoRdsRefundRepository;
use App\Services\Adaptor\Taobao\Repository\TaobaoRefundRepository;
use Illuminate\Support\Carbon;

class RefundDownloader implements DownloaderContract
{

    /**
     * @var TaobaoRefundRepository
     */
    private $repository;

    /**
     * @var TaobaoRdsRefundRepository
     */
    private $rds;

    public function __construct(TaobaoRdsRefundRepository $rds, TaobaoRefundRepository $repository)
    {
        $this->rds = $rds;
        $this->repository = $repository;
    }

    public function download($where)
    {
        if (isset($where['refund_ids']) && !empty($where['refund_ids'])) {
            $where[] = ['refund_id', 'in', $where['refund_ids']];
            unset($where['refund_ids']);
        }
        // 处理店铺
        if (empty($where['seller_nick'])) {
            $sellerNickShopCodeMap = [];
            $shops = Shop::available('taobao')->get();
            foreach ($shops as $shop) {
                $sellerNickShopCodeMap[] = $shop['seller_nick'];
            }
            if (!empty($sellerNickShopCodeMap)) {
                $where[] = ['seller_nick', 'in', $sellerNickShopCodeMap];
            }
        }
        $formatData = [];
        $fields = ['refund_id', 'oid', 'tid', 'seller_nick', 'status', 'jdp_response', 'jdp_created', 'jdp_modified', 'created', 'modified'];
        $refunds = $this->rds->getAll($where, $fields, 'jdp_modified asc,refund_id asc');
        $where = [];
        $where[] = ['refund_id', 'in', $refunds->pluck('refund_id')->toArray()];
        $existRefunds = $this->repository->getAll($where, ['refund_id', 'origin_modified']);
        if (!$existRefunds->isEmpty()) {
            $existRefunds = $existRefunds->keyBy('refund_id');
        }
        $updateRefunds = $insertRefunds = [];
        foreach ($refunds as $rdsRefund) {
            if (!$existRefunds->isEmpty() && isset($existRefunds[$rdsRefund->refund_id])) {
                if ($existRefunds[$rdsRefund->refund_id]->origin_modified >= strtotime($rdsRefund->jdp_modified)) {
                    // continue;
                }
                $updateRefunds[$rdsRefund->refund_id] = $existRefunds[$rdsRefund->refund_id];
            } else {
                $insertRefunds[] = $rdsRefund->refund_id;
            }
            $formatData[$rdsRefund->refund_id] = $this->format($rdsRefund);
        }
        if ($formatData) {
            $updateFields = ['status', 'origin_content', 'origin_modified', 'sync_status', 'updated_at', 'created', 'modified'];
            $this->repository->insertMulti($formatData, $updateFields);
            // 处理转入完成队列
        }
    }

    public function format($refund)
    {
        return [
            'refund_id'       => $refund->refund_id,
            'tid'             => $refund->tid,
            'seller_nick'     => $refund->seller_nick,
            'status'          => $refund->status,
            'oid'             => $refund->oid,
            'origin_content'  => $refund->jdp_response,
            'origin_created'  => strtotime($refund->jdp_created),
            'origin_modified' => strtotime($refund->jdp_modified),
            'sync_status'     => 0, // 未转入
            'created'         => $refund->created,
            'modified'        => $refund->modified,
            'created_at'      => Carbon::now()->toDateTimeString(),
            'updated_at'      => Carbon::now()->toDateTimeString(),
        ];
    }
}
