<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class TaobaoInvoiceApply
 * @package App\Models
 * @version June 10, 2020, 10:28 pm CST
 *
 * @property unsignedBigInteger apply_id
 * @property string platform_tid
 * @property string seller_nick
 * @property string platform_code
 * @property string trigger_status
 * @property unsignedTinyInteger business_type
 * @property unsignedTinyInteger query_status
 * @property timestamp query_at
 * @property timestamp next_query_at
 * @property unsignedTinyInteger push_status
 * @property string error_msg
 * @property timestamp pushed_at
 * @property unsignedTinyInteger upload_status
 * @property timestamp upload_at
 * @property json origin_content
 * @property json origin_detail
 * @property json origin_upload_detail
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class TaobaoInvoiceApply extends Model
{
    protected $table = 'taobao_invoice_apply';

    protected $primaryKey = 'apply_id';

    public $incrementing = false;

    const QUERY_STATUS_INIT = 0;

    const QUERY_STATUS_SUCCESS = 1;

    protected $fillable = [
        'apply_id',
        'platform_tid',
        'seller_nick',
        'platform_code',
        'trigger_status',
        'business_type',
        'query_status',
        'query_at',
        'next_query_at',
        'push_status',
        'pushed_at',
        'upload_status',
        'upload_at',
        'origin_content',
        'origin_detail',
        'origin_upload_detail',
        'error_msg',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'platform_tid' => 'string',
        'apply_id' => 'string',
        'seller_nick' => 'string',
        'platform_code' => 'string',
        'trigger_status' => 'string',
        'origin_content' => 'array',
        'origin_detail' => 'array',
        'origin_upload_detail' => 'array',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

}
