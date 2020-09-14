<?php


namespace App\Repository;


use Illuminate\Support\Facades\DB;

class BaseTaobaoRdsRepository
{

    public $db;
    public $builder;
    protected $table;

    public function __construct()
    {
        $name = config('taobao.rds_db');
        $this->db = DB::connection($name);
        $this->builder = $this->db->table($this->table);
    }

    public function find($id, $field = ['*'])
    {

    }

    public function update()
    {

    }

    public function insert($data)
    {

    }

    public function insertMulti($data, $ignore = false)
    {

    }
}