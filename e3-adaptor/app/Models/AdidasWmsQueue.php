<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class AdidasWmsQueue
 * @package App\Models
 * @version July 30, 2020, 11:12 am CST
 *
 * @property integer id
 * @property string bis_id
 * @property string wms
 * @property string method
 * @property unsignedTinyInteger status
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class AdidasWmsQueue extends Model
{
    protected $table = 'adidas_wms_queue';

    protected $fillable = [
        'id',
        'bis_id',
        'wms',
        'method',
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
        'bis_id' => 'string',
        'wms' => 'string',
        'method' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

}
