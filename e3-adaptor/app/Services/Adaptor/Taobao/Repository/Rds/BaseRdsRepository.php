<?php


namespace App\Services\Adaptor\Taobao\Repository\Rds;


use App\Services\Adaptor\BaseRepository;

class BaseRdsRepository extends BaseRepository
{
    protected $jsonFields = ['origin_content'];

    public function db()
    {
        // 连接信息
        $connection = config('adaptor.adaptors.taobao.rds.connection', 'taobao_rds');

        return \DB::connection($connection);
    }

    public function builder()
    {
        return $this->db()->table($this->table);
    }
}