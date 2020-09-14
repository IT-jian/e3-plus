<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class TaobaoTrade
 * @package App\Models
 * @version December 18, 2019, 2:09 pm CST
 *
 * @property unsignedInteger tid
 * @property string seller_nick
 * @property string status
 * @property string type
 * @property mediumText origin_content
 * @property unsignedInteger origin_created
 * @property unsignedInteger origin_modified
 */
class TaobaoTrade extends Model
{

    protected $table = 'taobao_trade';

    protected $primaryKey = 'tid';

    public $incrementing = false;

    protected $fillable = [
        'tid',
        'seller_nick',
        'sync_status',
        'status',
        'type',
        'origin_content',
        'origin_created',
        'origin_modified',
        'created',
        'modified',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'tid' => 'string',
        'seller_nick' => 'string',
        'status' => 'string',
        'type' => 'string',
        'origin_content' => 'array',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

}
