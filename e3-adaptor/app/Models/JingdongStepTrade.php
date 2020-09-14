<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class JingdongStepTrade
 * @package App\Models
 * @version May 2, 2020, 6:29 pm CST
 *
 * @property unsignedBigInteger id
 * @property unsignedBigInteger order_id
 * @property unsignedBigInteger presale_id
 * @property string shop_id
 * @property string order_status
 * @property string order_type
 * @property unsignedInteger create_time
 * @property string|\Carbon\Carbon update_time
 * @property mediumText origin_content
 * @property unsignedInteger origin_created
 * @property unsignedInteger origin_modified
 * @property unsignedTinyInteger sync_status
 * @property unsignedBigInteger version
 */
class JingdongStepTrade extends Model
{
    protected $table = 'jingdong_step_trade';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'order_id',
        'presale_id',
        'shop_id',
        'order_status',
        'order_type',
        'create_time',
        'update_time',
        'origin_content',
        'origin_created',
        'origin_modified',
        'sync_status',
        'version'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'shop_id' => 'string',
        'order_status' => 'string',
        'order_type' => 'string',
        'origin_content' => 'array',
        'order_status' => 'string',
        'order_type' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

}