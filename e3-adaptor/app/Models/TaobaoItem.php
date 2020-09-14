<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class TaobaoItem
 * @package App\Models
 * @version December 18, 2019, 3:30 pm CST
 *
 * @property unsignedBigInteger num_iid
 * @property string seller_nick
 * @property string status
 * @property json origin_content
 * @property unsignedInteger origin_created
 * @property unsignedInteger origin_modified
 */
class TaobaoItem extends Model
{

    protected $table = 'taobao_item';

    protected $primaryKey = 'num_iid';

    public $incrementing = false;

    protected $fillable = [
        'num_iid',
        'seller_nick',
        'status',
        'sync_status',
        'origin_content',
        'origin_created',
        'origin_modified',
        'origin_delete',
        'sync_status',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'num_iid' => 'string',
        'seller_nick' => 'string',
        'status' => 'string',
        'origin_content' => 'array',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

}