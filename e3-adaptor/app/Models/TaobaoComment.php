<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class TaobaoComment
 * @package App\Models
 * @version May 9, 2020, 3:24 pm CST
 *
 * @property unsignedInteger tid
 * @property unsignedInteger oid
 * @property string num_iid
 * @property timestamp created
 * @property mediumText origin_content
 * @property unsignedTinyInteger sync_status
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class TaobaoComment extends Model
{
    protected $table = 'taobao_comment';

    public $incrementing = false;

    protected $fillable = [
        'tid',
        'oid',
        'num_iid',
        'created',
        'origin_content',
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
        'num_iid' => 'string',
        'tid' => 'string',
        'oid' => 'string',
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
