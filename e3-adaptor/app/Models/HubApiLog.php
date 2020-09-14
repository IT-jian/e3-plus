<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class HubApiLog
 * @package App\Models
 * @version January 2, 2020, 9:39 pm CST
 *
 * @property integer id
 * @property string api_method
 * @property string ip
 * @property string partner
 * @property string platform
 * @property string input
 * @property string response
 * @property timestamp start_at
 * @property timestamp updated_at
 */
class HubApiLog extends Model
{
    protected $table = 'hub_api_log';

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