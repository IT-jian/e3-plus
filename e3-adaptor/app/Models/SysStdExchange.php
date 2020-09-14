<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class SysStdExchange
 * @package App\Models
 * @version December 24, 2019, 2:14 pm CST
 *
 * @property unsignedBigInteger dispute_id
 * @property string platform
 * @property unsignedBigInteger tid
 * @property string shop_code
 * @property string status
 * @property string refund_phase
 * @property string refund_version
 * @property string buyer_name
 * @property string buyer_address
 * @property string buyer_phone
 * @property string buyer_logistic_name
 * @property string buyer_logistic_no
 * @property string seller_address
 * @property string seller_logistic_name
 * @property string seller_logistic_no
 * @property string|\Carbon\Carbon created
 * @property string|\Carbon\Carbon modified
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class SysStdExchange extends Model
{
    protected $table = 'sys_std_exchange';

    protected $primaryKey = 'dispute_id';

    public $incrementing = false;

    protected $fillable = [
        'dispute_id',
        'platform',
        'tid',
        'shop_code',
        'status',
        'refund_phase',
        'refund_version',
        'buyer_name',
        'buyer_address',
        'buyer_phone',
        'buyer_logistic_name',
        'buyer_logistic_no',
        'seller_address',
        'seller_logistic_name',
        'seller_logistic_no',
        'created',
        'modified',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'dispute_id' => 'string',
        'tid' => 'string',
        'platform' => 'string',
        'shop_code' => 'string',
        'status' => 'string',
        'refund_phase' => 'string',
        'refund_version' => 'string',
        'buyer_name' => 'string',
        'buyer_address' => 'string',
        'buyer_phone' => 'string',
        'buyer_logistic_name' => 'string',
        'buyer_logistic_no' => 'string',
        'seller_address' => 'string',
        'seller_logistic_name' => 'string',
        'seller_logistic_no' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    /**
     * 含有多个退单明细
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(\App\Models\SysStdExchangeItem::class, 'dispute_id', 'dispute_id');
    }


    /**
     * 属于订单
     * trade
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function trade()
    {
        return $this->belongsTo(\App\Models\SysStdTrade::class, 'tid', 'tid');
    }

}