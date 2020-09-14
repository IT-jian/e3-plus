<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class TaobaoExchange
 * @package App\Models
 * @version December 18, 2019, 3:29 pm CST
 *
 * @property unsignedBigInteger dispute_id
 * @property string seller_nick
 * @property unsignedBigInteger biz_order_id
 * @property string status
 * @property json origin_content
 * @property unsignedInteger origin_created
 * @property unsignedInteger origin_modified
 */
class TaobaoExchange extends Model
{
    protected $table = 'taobao_exchange';

    protected $primaryKey = 'dispute_id';

    public $incrementing = false;

    protected $fillable = [
        'dispute_id',
        'seller_nick',
        'biz_order_id',
        'status',
        'sync_status',
        'origin_content',
        'origin_created',
        'origin_modified',
        'created',
        'modified',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'seller_nick' => 'string',
        'dispute_id' => 'string',
        'biz_order_id' => 'string',
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
