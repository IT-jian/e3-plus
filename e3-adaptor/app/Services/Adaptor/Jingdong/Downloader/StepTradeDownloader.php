<?php


namespace App\Services\Adaptor\Jingdong\Downloader;


use App\Services\Adaptor\Contracts\DownloaderContract;
use App\Services\Adaptor\Jingdong\Repository\JingdongStepTradeRepository;
use Carbon\Carbon;

class StepTradeDownloader implements DownloaderContract
{
    private $repository;

    public function __construct(JingdongStepTradeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function download($trades)
    {
        $formatData = [];
        foreach ($trades as $trade) {
            $formatData[] = $this->formatTrade($trade);
        }
        $updateFields = [
            'order_status',
            'create_time',
            'update_time',
            'version',
            'origin_content',
            'origin_created',
            'origin_modified',
            'sync_status',
            'updated_at',
        ];
        return $this->repository->insertMulti($formatData, $updateFields);
    }

    public function formatTrade($trade)
    {
        $now = Carbon::now();
        return [
            'id'              => $trade['id'],
            'order_id'        => $trade['orderId'],
            'presale_id'      => $trade['presaleId'],
            'shop_id'         => $trade['shopId'],
            'order_status'    => $trade['orderStatus'],
            'order_type'      => $trade['orderType'],
            'create_time'     => $trade['createTime'],
            'update_time'     => $trade['updateTime'],
            'version'         => strtotime($trade['updateTime']),
            'origin_content'  => json_encode($trade),
            'origin_created'  => $now->timestamp,
            'origin_modified' => $now->timestamp,
            'sync_status'     => 0,
            'created_at'      => $now->toDateTimeString(),
            'updated_at'      => $now->toDateTimeString(),
        ];
    }
}