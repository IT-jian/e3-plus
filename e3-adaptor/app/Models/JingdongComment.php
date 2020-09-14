<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class JingdongComment
 * @package App\Models
 * @version May 3, 2020, 8:17 pm CST
 *
 * @property string comment_id
 * @property string sku_id
 * @property mediumText origin_content
 * @property unsignedInteger origin_created
 * @property unsignedTinyInteger sync_status
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class JingdongComment extends Model
{
    protected $table = 'jingdong_comment';

    protected $primaryKey = 'comment_id';

    public $incrementing = false;

    protected $fillable = [
        'comment_id',
        'sku_id',
        'vender_id',
        'order_id',
        'origin_content',
        'origin_created',
        'sync_status',
        'create_time',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'comment_id' => 'string',
        'order_id' => 'string',
        'vender_id' => 'string',
        'sku_id' => 'string',
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