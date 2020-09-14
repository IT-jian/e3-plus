<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class JingdongRefund
 * @package App\Models
 * @version May 3, 2020, 8:17 pm CST
 *
 * @property unsignedBigInteger service_id
 * @property string vender_id
 * @property string order_id
 * @property string service_status
 * @property string customer_expect
 * @property date apply_time
 * @property date update_date
 * @property mediumText origin_content
 * @property unsignedInteger origin_created
 * @property unsignedInteger origin_modified
 * @property unsignedTinyInteger sync_status
 * @property unsignedBigInteger sys_version
 */
class JingdongRefund extends Model
{

    protected $table = 'jingdong_refund';

    protected $primaryKey = 'service_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'service_id',
        'vender_id',
        'order_id',
        'service_status',
        'customer_expect',
        'apply_time',
        'update_date',
        'change_sku',
        'origin_content',
        'origin_created',
        'origin_modified',
        'sync_status',
        'sys_version'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'vender_id' => 'string',
        'order_id' => 'string',
        'service_status' => 'string',
        'customer_expect' => 'string',
        'change_sku' => 'string',
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
