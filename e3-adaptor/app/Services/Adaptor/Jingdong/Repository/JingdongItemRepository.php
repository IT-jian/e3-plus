<?php


namespace App\Services\Adaptor\Jingdong\Repository;

use App\Services\Adaptor\BaseRepository;

/**
 * Class JingdongItemRepository
 *
 * @package App\Services\Adaptor\Jingdong\Repository
 */
class JingdongItemRepository extends BaseRepository
{
    protected $table = 'jingdong_item';

    public function updateSyncStatus($wareIds, $status = 1)
    {
        $this->db()->table($this->table)->whereIn('ware_id', $wareIds)->update(['sync_status' => $status]);
    }
}
