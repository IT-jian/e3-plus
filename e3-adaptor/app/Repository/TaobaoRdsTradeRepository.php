<?php


namespace App\Repository;



class TaobaoRdsTradeRepository extends BaseTaobaoRdsRepository
{
    protected $table = 'jdp_tb_table';

    public function __construct()
    {
        parent::__construct();
    }

    public function get($where = "", $fields = '*', $orderBy = 'jdp_modified', $page = 1, $pageSize = 100)
    {
        $sql = "SELECT {$fields} FROM {$this->table} WHERE {$where}";
    }
}