<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class JingdongRefundApply
 * @package App\Models
 * @version May 4, 2020, 11:13 pm CST
 *
 * @property unsignedBigInteger id
 * @property string vender_id
 * @property string order_id
 * @property string status
 * @property string reason
 * @property date apply_time
 * @property date check_time
 * @property mediumText origin_content
 * @property unsignedInteger origin_created
 * @property unsignedInteger origin_modified
 * @property unsignedTinyInteger sync_status
 */
class JingdongRefundApply extends Model
{
    protected $table = 'jingdong_refund_apply';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'vender_id',
        'order_id',
        'status',
        'reason',
        'apply_time',
        'check_time',
        'origin_content',
        'origin_created',
        'origin_modified',
        'sync_status'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'vender_id' => 'string',
        'order_id' => 'string',
        'status' => 'string',
        'reason' => 'string',
        'apply_time' => 'datetime',
        'check_time' => 'datetime',
        'origin_content' => 'array'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

}
