<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class SkuInventoryApiLog
 * @package App\Models
 * @version July 31, 2020, 5:08 pm CST
 *
 * @property integer id
 * @property string api_method
 * @property string ip
 * @property string partner
 * @property string platform
 * @property string input
 * @property string response
 * @property timestamp start_at
 * @property timestamp end_at
 */
class SkuInventoryApiLog extends Model
{
    protected $table = 'sku_inventory_api_log';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'api_method',
        'request_id',
        'ip',
        'partner',
        'platform',
        'input',
        'response',
        'start_at',
        'end_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'api_method' => 'string',
        'request_id' => 'string',
        'ip' => 'string',
        'partner' => 'string',
        'platform' => 'string',
        'input' => 'array',
        'response' => 'array',
        'start_at'=> 'datetime',
        'end_at'=> 'datetime'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

}
