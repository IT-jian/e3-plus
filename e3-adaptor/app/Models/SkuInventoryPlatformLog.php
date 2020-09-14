<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class SkuInventoryPlatformLog
 * @package App\Models
 * @version August 17, 2020, 10:25 am CST
 *
 * @property string id
 * @property string num_iid
 * @property string batch_version
 * @property array skus
 * @property array request
 * @property array response
 * @property string start_at
 * @property string end_at
 */
class SkuInventoryPlatformLog extends Model
{
    protected $table = 'sku_inventory_platform_log';

    protected $fillable = [
        'id',
        'num_iid',
        'skus',
        'shop_code',
        'update_type',
        'batch_version',
        'request',
        'response',
        'notice_content',
        'response_status',
        'start_at',
        'end_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'num_iid' => 'string',
        'response_status' => 'int',
        'batch_version' => 'string',
        'skus' => 'array',
        'request' => 'array',
        'response' => 'array',
        'notice_content' => 'array'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

}
