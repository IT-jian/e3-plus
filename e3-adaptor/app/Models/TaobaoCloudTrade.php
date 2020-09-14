<?php

namespace App\Models;

use App\Models\BaseModel as Model;

class TaobaoCloudTrade extends Model
{
    // 淘宝RDS
    protected $connection = 'taobao_rds';
    protected $table = 'jdp_tb_trade';

    protected $casts = [
        'jdp_response' => 'array'
    ];
}
