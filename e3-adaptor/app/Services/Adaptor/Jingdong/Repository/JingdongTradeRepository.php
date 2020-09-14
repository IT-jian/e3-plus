<?php


namespace App\Services\Adaptor\Jingdong\Repository;

use App\Services\Adaptor\BaseRepository;

/**
 * Class JingdongTradeRepository
 *
 * @package App\Services\Adaptor\Jingdong\Repository
 */
class JingdongTradeRepository extends BaseRepository
{
    protected $table = 'jingdong_trade';

    public function updateSyncStatus($orderIds, $status = 1)
    {
        $this->db()->table($this->table)->whereIn('order_id', $orderIds)->update(['sync_status' => $status]);
    }
}