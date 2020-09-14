<?php


namespace App\Services\Adaptor\Taobao\Repository;

use App\Services\Adaptor\BaseRepository;

/**
 * Class TaobaoItemRepository
 *
 * @package App\Services\Adaptor\Taobao\Repository
 *
 * @author linqihai
 * @since 2019/12/25 15:19
 */
class TaobaoItemRepository extends BaseRepository
{
    protected $table = 'taobao_item';

    public function updateSyncStatus($num_iids, $status = 1)
    {
        $this->db()->table($this->table)->whereIn('num_iid', $num_iids)->update(['sync_status' => $status]);
    }
}
