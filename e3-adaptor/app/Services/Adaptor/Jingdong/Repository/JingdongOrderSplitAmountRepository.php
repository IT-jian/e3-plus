<?php


namespace App\Services\Adaptor\Jingdong\Repository;

use App\Services\Adaptor\BaseRepository;

/**
 * Class JingdongOrderSplitAmountRepository
 *
 * @package App\Services\Adaptor\Jingdong\Repository
 */
class JingdongOrderSplitAmountRepository extends BaseRepository
{
    protected $table = 'jingdong_order_split_amount';

    public function updateSyncStatus($commentIds, $status = 1)
    {
        $this->db()->table($this->table)->whereIn('comment_id', $commentIds)->update(['sync_status' => $status]);
    }
}