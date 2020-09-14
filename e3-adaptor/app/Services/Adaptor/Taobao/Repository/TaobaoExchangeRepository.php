<?php


namespace App\Services\Adaptor\Taobao\Repository;

use App\Services\Adaptor\BaseRepository;

/**
 * Class TaobaoExchangeRepository
 *
 * @package App\Services\Adaptor\Taobao\Repository
 *
 * @author linqihai
 * @since 2019/12/25 15:19
 */
class TaobaoExchangeRepository extends BaseRepository
{
    protected $table = 'taobao_exchange';

    public function updateSyncStatus($disputeIds, $status = 1)
    {
        $this->db()->table($this->table)->whereIn('dispute_id', $disputeIds)->update(['sync_status' => $status]);
    }
}
