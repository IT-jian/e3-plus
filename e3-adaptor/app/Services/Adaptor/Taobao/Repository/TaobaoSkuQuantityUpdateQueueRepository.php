<?php


namespace App\Services\Adaptor\Taobao\Repository;

use App\Services\Adaptor\BaseRepository;

/**
 * Class TaobaoSkuQuantityUpdateQueueRepository
 *
 * @package App\Services\Adaptor\Taobao\Repository
 *
 * @author linqihai
 * @since 2020/08/17 13:29
 */
class TaobaoSkuQuantityUpdateQueueRepository extends BaseRepository
{
    protected $table = 'taobao_skus_quantity_update_queue';

    public function updateStatus($queueIds, $status = 1, $message = '')
    {
        return $this->db()->table($this->table)->whereIn('id', $queueIds)->increment('try_times', 1, ['status' => $status, 'message' => $message]);
    }
}
