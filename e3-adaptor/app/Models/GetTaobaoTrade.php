<?php

namespace App\Models;

use App\Models\BaseModel as Model;

class GetTaobaoTrade extends Model
{
    protected $table = 'get_taobao_trade';

    protected $fillable = [
        'status', 'sync_status'
    ];
}
