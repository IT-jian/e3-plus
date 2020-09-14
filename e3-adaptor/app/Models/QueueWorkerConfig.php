<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class QueueWorkerConfig
 * @package App\Models
 * @version January 16, 2020, 5:35 pm CST
 *
 * @property integer id
 * @property string code
 * @property string name
 * @property integer process_number
 * @property string command
 * @property string user
 * @property integer status
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class QueueWorkerConfig extends Model
{
    protected $table = 'queue_worker_config';

    protected $fillable = [
        'id',
        'code',
        'name',
        'process_number',
        'command',
        'user',
        'status',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'code' => 'string',
        'name' => 'string',
        'process_number' => 'integer',
        'command' => 'string',
        'user' => 'string',
        'status' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'code' => 'required',
        'name' => 'required',
        'process_number' => 'required',
        'command' => 'required',
        'user' => 'required',
    ];

}