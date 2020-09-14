<?php


namespace App\Services\Adaptor\Jingdong\Repository;

use App\Services\Adaptor\BaseRepository;

/**
 * Class JingdongCommentsRepository
 *
 * @package App\Services\Adaptor\Jingdong\Repository
 */
class JingdongCommentsRepository extends BaseRepository
{
    protected $table = 'jingdong_comment';

    public function updateSyncStatus($commentIds, $status = 1)
    {
        $this->db()->table($this->table)->whereIn('comment_id', $commentIds)->update(['sync_status' => $status]);
    }
}