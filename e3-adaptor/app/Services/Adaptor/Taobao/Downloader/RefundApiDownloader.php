<?php


namespace App\Services\Adaptor\Taobao\Downloader;


use App\Models\Sys\Shop;
use App\Services\Adaptor\Contracts\DownloaderContract;
use App\Services\Adaptor\Taobao\Api\RefundApi;
use App\Services\Adaptor\Taobao\Repository\Rds\TaobaoRdsRefundRepository;
use App\Services\Adaptor\Taobao\Repository\TaobaoRefundRepository;
use Illuminate\Support\Carbon;

class RefundApiDownloader implements DownloaderContract
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

    public function download($params)
    {
        $formatData = [];
        $shopCode = $params['shopCode'] ?? '';
        if (empty($shopCode)) {
            throw new \Exception('API下载需要指定店铺，下载失败！');
        }
        $refundIds = $params['refund_ids'];

        $where = [];
        $where[] = ['refund_id', 'in', $refundIds];
        $existRefunds = $this->repository->getAll($where, ['refund_id', 'origin_modified']);
        if (!$existRefunds->isEmpty()) {
            $refundIds = array_diff($refundIds, $existRefunds->pluck('refund_id')->toArray());
        }
        if (empty($refundIds)) {
            return true;
        }
        $refunds = $this->queryFromApi($refundIds, $shopCode);

        foreach ($refunds as $refund) {
            $formatData[$refund['refund_id']] = $this->format($refund);
        }
        if ($formatData) {
            $updateFields = ['status', 'origin_content', 'sync_status', 'updated_at', 'created', 'modified'];
            $this->repository->insertMulti($formatData, $updateFields);
        }

        return true;
    }

    public function format($refund)
    {
        return [
            'refund_id'       => $refund['refund_id'],
            'tid'             => $refund['tid'],
            'seller_nick'     => $refund['seller_nick'],
            'status'          => $refund['status'],
            'oid'             => $refund['oid'],
            'origin_content'  => $refund['jdp_response'],
            'origin_created'  => strtotime($refund['jdp_created']),
            'origin_modified' => strtotime($refund['jdp_modified']),
            'created'         => $refund['created'],
            'modified'        => $refund['modified'],
            'sync_status'     => 0, // 未转入
            'created_at'      => Carbon::now()->toDateTimeString(),
            'updated_at'      => Carbon::now()->toDateTimeString(),
        ];
    }


    public function queryFromApi($refundIds, $shopCode)
    {
        $formatTrade = [];
        $shop = Shop::getShopByCode($shopCode);
        foreach (array_chunk($refundIds, 50) as $chunkRefundId) {
            $refunds = (new RefundApi($shop))->find($chunkRefundId);
            foreach ($refunds as $originRefund) {
                $refund = data_get($originRefund, 'refund_get_response.refund', []);
                if (empty($refund)) {
                    continue;
                }
                $formatTrade[] = [
                    'refund_id'       => $refund['refund_id'],
                    'tid'             => $refund['tid'],
                    'seller_nick'     => $refund['seller_nick'],
                    'status'          => $refund['status'],
                    'oid'             => $refund['oid'],
                    'origin_content'  => json_encode($originRefund),
                    'origin_created'  => strtotime($refund['created']),
                    'origin_modified' => strtotime($refund['modified']),
                    'created'         => $refund['created'],
                    'modified'        => $refund['modified'],
                    'sync_status'     => 0, // 未转入
                    'created_at'      => Carbon::now()->toDateTimeString(),
                    'updated_at'      => Carbon::now()->toDateTimeString(),
                ];
            }
        }

        return $formatTrade;
    }
}
