<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class OperationLog
 *
 * @package App\Models
 * @version May 23, 2018, 2:43 pm CST
 * @property integer id
 * @property integer user_id
 * @property string path
 * @property string method
 * @property string ip
 * @property string input
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OperationLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OperationLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OperationLog query()
 */
class OperationLog extends Model
{
    protected $connection = 'mysql';
    public $table = 'operation_logs';

    public $fillable = [
        'id',
        'user_id',
        'path',
        'method',
        'ip',
        'input',
        'response'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'path' => 'string',
        'method' => 'string',
        'ip' => 'string',
        'input' => 'array',
        'response' => 'array'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'user_id' => 'required',
        'path' => 'required',
        'method' => 'required',
        'ip' => 'required'
    ];

}