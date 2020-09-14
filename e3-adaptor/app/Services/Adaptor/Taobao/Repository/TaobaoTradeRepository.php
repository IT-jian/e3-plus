<?php


namespace App\Services\Adaptor\Taobao\Repository;

use App\Services\Adaptor\BaseRepository;

/**
 * Class TaobaoTradeRepository
 * @package App\Services\Adaptor\Taobao\Repository
 *
 * @author linqihai
 * @since 2019/12/25 15:19
 */
class TaobaoTradeRepository extends BaseRepository
{
    protected $table = 'taobao_trade';

    public function updateSyncStatus($tids, $status = 1)
    {
        $this->db()->table($this->table)->whereIn('tid', $tids)->update(['sync_status' => $status]);
    }
}