<?php


namespace App\Services\Adaptor\Jingdong\Repository;

use App\Services\Adaptor\BaseRepository;

/**
 * 京东预售订单中间表
 *
 * Class JingdongStepTradeRepository
 *
 * @package App\Services\Adaptor\Jingdong\Repository
 */
class JingdongStepTradeRepository extends BaseRepository
{
    protected $table = 'jingdong_step_trade';

    public function updateSyncStatus($orderIds, $status = 1)
    {
        $this->db()->table($this->table)->whereIn('order_id', $orderIds)->update(['sync_status' => $status]);
    }
}