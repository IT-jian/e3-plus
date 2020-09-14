<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class SysStdTrade
 * @package App\Models
 * @version December 19, 2019, 4:18 pm CST
 *
 * @property unsignedInteger tid
 * @property string shop_code
 * @property decimal total_fee
 * @property decimal discount_fee
 * @property decimal payment
 * @property string pay_type
 * @property string pay_status
 * @property decimal post_fee
 * @property string receiver_name
 * @property string receiver_country
 * @property string receiver_state
 * @property string receiver_city
 * @property string receiver_district
 * @property string receiver_town
 * @property string receiver_address
 * @property string receiver_zip
 * @property string receiver_mobile
 * @property string receiver_phone
 * @property string status
 * @property string type
 * @property string buyer_nick
 * @property tinyInt seller_flag
 * @property string seller_memo
 * @property string buyer_message
 * @property string step_trade_status
 * @property decimal step_paid_fee
 * @property string|\Carbon\Carbon pay_time
 * @property string|\Carbon\Carbon created
 * @property string|\Carbon\Carbon modified
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class SysStdTrade extends Model
{
    use PlatformTrait;

    protected $table = 'sys_std_trade';

    protected $primaryKey = 'tid';

    public $incrementing = false;

    protected $fillable = [
        'tid',
        'platform',
        'shop_code',
        'total_fee',
        'discount_fee',
        'coupon_fee',
        'pay_no',
        'payment',
        'pay_type',
        'pay_status',
        'post_fee',
        'receiver_name',
        'receiver_country',
        'receiver_state',
        'receiver_city',
        'receiver_district',
        'receiver_town',
        'receiver_address',
        'receiver_zip',
        'receiver_mobile',
        'receiver_phone',
        'buyer_email',
        'status',
        'type',
        'buyer_nick',
        'seller_flag',
        'seller_memo',
        'buyer_message',
        'step_trade_status',
        'step_paid_fee',
        'pay_time',
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
        'shop_code' => 'string',
        'platform' => 'string',
        'tid' => 'string',
        'pay_no' => 'string',
        'pay_type' => 'string',
        'pay_status' => 'string',
        'receiver_name' => 'string',
        'receiver_country' => 'string',
        'receiver_state' => 'string',
        'receiver_city' => 'string',
        'receiver_district' => 'string',
        'receiver_town' => 'string',
        'receiver_address' => 'string',
        'receiver_zip' => 'string',
        'receiver_mobile' => 'string',
        'receiver_phone' => 'string',
        'buyer_email' => 'string',
        'status' => 'string',
        'type' => 'string',
        'buyer_nick' => 'string',
        'seller_memo' => 'string',
        'buyer_message' => 'string',
        'step_trade_status' => 'string',
        'created' => 'datetime',
        'modified' => 'datetime',

        'total_fee'     => 'decimal:2',
        'discount_fee'  => 'decimal:2',
        'payment'       => 'decimal:2',
        'post_fee'      => 'decimal:2',
        'step_paid_fee' => 'decimal:2',
        'coupon_fee'    => 'decimal:2',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    /**
     * 明细
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(\App\Models\SysStdTradeItem::class, 'tid', 'tid');
    }

    /**
     * 明细
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function promotions()
    {
        return $this->hasMany(\App\Models\SysStdTradePromotion::class, 'tid', 'tid');
    }

    /**
     * 包含多个退单
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function refund()
    {
        return $this->hasMany(\App\Models\SysStdRefund::class, 'tid', 'tid');
    }

    /**
     * 包含仅退款
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function onlyRefund()
    {
        return $this->hasMany(\App\Models\SysStdRefund::class, 'tid', 'tid')->where('has_good_return', 0);
    }
}
