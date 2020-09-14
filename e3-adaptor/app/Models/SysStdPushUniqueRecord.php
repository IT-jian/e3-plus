<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class SysStdPushUniqueRecord
 * @package App\Models
 * @version March 2, 2020, 7:52 pm CST
 *
 * @property string bis_id
 * @property string platform
 * @property string method
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class SysStdPushUniqueRecord extends Model
{
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];
    protected $table = 'sys_std_push_unique_record';
    protected $fillable = [
        'bis_id',
        'platform',
        'method',
        'created_at',
        'updated_at',
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'bis_id'   => 'string',
        'platform' => 'string',
        'method'   => 'string',
    ];

}