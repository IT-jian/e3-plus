<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class JingdongItem
 * @package App\Models
 * @version June 14, 2020, 5:23 pm CST
 *
 * @property integer ware_id
 * @property string vender_id
 * @property integer ware_status
 * @property array origin_content
 * @property integer origin_created
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class JingdongItem extends Model
{
    protected $table = 'jingdong_item';

    protected $primaryKey = 'ware_id';

    public $incrementing = false;

    protected $fillable = [
        'ware_id',
        'vender_id',
        'ware_status',
        'origin_content',
        'origin_created',
        'origin_modified',
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
        'ware_id' => 'string',
        'vender_id' => 'string',
        'sync_status' => 'string',
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
