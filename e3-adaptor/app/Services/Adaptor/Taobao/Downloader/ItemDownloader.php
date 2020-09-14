<?php


namespace App\Services\Adaptor\Taobao\Downloader;


use App\Services\Adaptor\Contracts\DownloaderContract;
use App\Services\Adaptor\Taobao\Repository\Rds\TaobaoRdsItemRepository;
use App\Services\Adaptor\Taobao\Repository\TaobaoItemRepository;
use Illuminate\Support\Carbon;

class ItemDownloader implements DownloaderContract
{
    /**
     * @var TaobaoItemRepository
     */
    private $repository;

    /**
     * @var TaobaoRdsItemRepository
     */
    private $rds;

    public function __construct(TaobaoRdsItemRepository $rds, TaobaoItemRepository $repository)
    {
        $this->rds = $rds;
        $this->repository = $repository;
    }

    /**
     * 根据 num_iid 下载
     *
     * @param $where
     * @return bool|int
     *
     * @author linqihai
     * @since 2019/12/23 9:35
     */
    public function download($where)
    {
        if (isset($where['num_iids']) && !empty($where['num_iids'])) {
            $where[] = ['num_iid', 'in', $where['num_iids']];
            unset($where['num_iids']);
        }
        $formatData = [];
        $fields = ['num_iid', 'nick as seller_nick', 'approve_status as status', 'jdp_response', 'jdp_created', 'jdp_modified', 'jdp_delete'];
        $items = $this->rds->getAll($where, $fields, 'jdp_modified asc');
        $where = [];
        $where[] = ['num_iid', 'in', $items->pluck('num_iid')->toArray()];
        $existNumIids = $this->repository->getAll($where, ['num_iid', 'origin_modified']);
        if (!$existNumIids->isEmpty()) {
            $existNumIids = $existNumIids->keyBy('num_iid');
        }
        foreach ($items as $item) {
            if (!$existNumIids->isEmpty() && isset($existNumIids[$item->num_iid])) {
                if ($existNumIids[$item->num_iid]->origin_modified >= strtotime($item->jdp_modified)) {
                    // continue;
                }
            }
            $formatData[$item->num_iid] = $this->format($item);
        }
        if ($formatData) {
            $updateFields = ['status', 'origin_content', 'origin_modified', 'sync_status', 'updated_at', 'origin_delete'];
            $this->repository->insertMulti($formatData, $updateFields);
            // 处理转入完成队列
        }

        return true;
    }


    /**
     * 格式转换
     * @param $item
     * @return array
     *
     * @author linqihai
     * @since 2019/12/23 9:35
     */
    public function format($item)
    {
        return [
            'num_iid'         => $item->num_iid,
            'seller_nick'     => $item->seller_nick,
            'status'          => $item->status,
            'origin_content'  => $item->jdp_response,
            'origin_created'  => strtotime($item->jdp_created),
            'origin_modified' => strtotime($item->jdp_modified),
            'origin_delete'     => $item->jdp_delete, // 是否已经删除
            'sync_status'     => 0, // 未转入
            'created_at'      => Carbon::now()->toDateTimeString(),
            'updated_at'      => Carbon::now()->toDateTimeString(),
        ];
    }

    public function downByTimeRange()
    {

    }
}
