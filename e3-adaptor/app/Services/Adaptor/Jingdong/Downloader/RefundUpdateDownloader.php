<?php


namespace App\Services\Adaptor\Jingdong\Downloader;


use App\Services\Adaptor\Contracts\DownloaderContract;
use App\Services\Adaptor\Jingdong\Api\RefundDetail;
use Illuminate\Support\Collection;

class RefundUpdateDownloader extends RefundDownloader implements DownloaderContract
{
    public function download($params)
    {
        $refunds = $params['refunds'];
        $this->shop = $params['shop'];
        $refunds = new Collection($refunds);
        $serviceIdArr = $refunds->pluck('serviceId')->toArray();
        $refunds = $refunds->keyBy('serviceId');
        $where = ['service_id', 'in', $serviceIdArr];
        $existRefunds = $this->repository->getAll($where);
        if ($existRefunds->isNotEmpty()) {
            $existRefunds = $existRefunds->keyBy('service_id');
        }
        $detailParams = [];
        foreach ($refunds as $refund) {
            if (isset($existRefunds[$refunds['serviceId']])) { // 已存在
                $existRefund = $existRefunds[$refunds['serviceId']];
                if ($existRefund['sys_version'] == $refund['sysVersion']) {
                    // continue;
                }
                $detailParams[$refund['serviceId']] = [
                    'order_id' => $refund['orderId'],
                    'service_id' => $refund['serviceId'],
                ];
            } else {
                // @todo 不存在的单据如何处理
            }
        }
        if (!empty($detailParams)) {
            $detailRequest = new RefundDetail($this->shop);
            $refundDetails = $detailRequest->multi($detailParams);
            $this->saveRefundDetails($refundDetails);
        }

        return true;
    }
}
