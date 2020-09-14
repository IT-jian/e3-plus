<?php


namespace App\Services\Adaptor\Jingdong\Repository\Rds;


class JingdongRdsTradeRepository extends BaseRdsRepository
{
    protected $table = 'yd_pop_order';

    public function __construct()
    {
        $this->table = config('adaptor.adaptors.jingdong.rds.trade_table', 'yd_pop_order');
    }
}