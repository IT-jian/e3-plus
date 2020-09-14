<?php


namespace App\Services\Adaptor\Jingdong\Repository;

use App\Services\Adaptor\BaseRepository;

/**
 * Class JingdongRefundApplyRepository
 *
 * @package App\Services\Adaptor\Jingdong\Repository
 */
class JingdongRefundApplyRepository extends BaseRepository
{
    protected $table = 'jingdong_refund_apply';

    public function updateSyncStatus($ids, $status = 1)
    {
        $this->db()->table($this->table)->whereIn('id', $ids)->update(['sync_status' => $status]);
    }
}