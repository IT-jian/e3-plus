<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class JingdongOrderSplitAmount
 * @package App\Models
 * @version May 5, 2020, 9:30 pm CST
 *
 * @property string orderId
 * @property mediumText origin_content
 * @property unsignedInteger origin_created
 * @property unsignedTinyInteger sync_status
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class JingdongOrderSplitAmount extends Model
{
    protected $table = 'jingdong_order_split_amount';

    protected $primaryKey = 'order_id';

    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'origin_content',
        'origin_created',
        'sync_status',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'orderId' => 'string',
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