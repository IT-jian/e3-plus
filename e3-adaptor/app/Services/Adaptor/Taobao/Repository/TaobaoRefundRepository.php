<?php


namespace App\Services\Adaptor\Taobao\Repository;

use App\Services\Adaptor\BaseRepository;

/**
 * Class TaobaoRefundRepository
 *
 * @package App\Services\Adaptor\Taobao\Repository
 *
 * @author linqihai
 * @since 2019/12/25 15:19
 */
class TaobaoRefundRepository extends BaseRepository
{
    protected $table = 'taobao_refund';

    public function updateSyncStatus($refundIds, $status = 1)
    {
        $this->db()->table($this->table)->whereIn('refund_id', $refundIds)->update(['sync_status' => $status]);
    }
}