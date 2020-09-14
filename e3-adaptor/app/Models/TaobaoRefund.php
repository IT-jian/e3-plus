<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class TaobaoRefund
 * @package App\Models
 * @version December 18, 2019, 3:11 pm CST
 *
 * @property unsignedBigInteger refund_id
 * @property string seller_nick
 * @property unsignedBigInteger tid
 * @property unsignedBigInteger oid
 * @property string status
 * @property mediumText origin_content
 * @property unsignedInteger origin_created
 * @property unsignedInteger origin_modified
 */
class TaobaoRefund extends Model
{
    protected $table = 'taobao_refund';

    protected $primaryKey = 'refund_id';

    public $incrementing = false;

    protected $fillable = [
        'refund_id',
        'seller_nick',
        'tid',
        'oid',
        'status',
        'sync_status',
        'origin_content',
        'origin_created',
        'origin_modified',
        'sync_status',
        'created',
        'modified',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'refund_id' => 'string',
        'tid' => 'string',
        'oid' => 'string',
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
