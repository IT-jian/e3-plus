<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class SysStdRefund
 * @package App\Models
 * @version December 22, 2019, 9:12 pm CST
 *
 * @property unsignedBigInteger refund_id
 * @property unsignedBigInteger tid
 * @property unsignedBigInteger oid
 * @property string shop_code
 * @property string status
 * @property string refund_phase
 * @property string refund_version
 * @property decimal refund_fee
 * @property string company_name
 * @property string sid
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class SysStdRefund extends Model
{
    use PlatformTrait;

    protected $table = 'sys_std_refund';

    protected $primaryKey = 'refund_id';

    public $incrementing = false;

    protected $fillable = [
        'refund_id',
        'platform',
        'tid',
        'oid',
        'shop_code',
        'status',
        'order_status',
        'refund_phase',
        'refund_version',
        'refund_fee',
        'company_name',
        'has_good_return',
        'sid',
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
        'refund_id' => 'string',
        'tid' => 'string',
        'oid' => 'string',
        'shop_code' => 'string',
        'status' => 'string',
        'order_status' => 'string',
        'refund_phase' => 'string',
        'refund_version' => 'string',
        'company_name' => 'string',
        'sid' => 'string',
        'has_good_return' => 'boole',
        'created' => 'datetime',
        'modified' => 'datetime',

        'refund_fee' => 'decimal:2',
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
        return $this->hasMany(\App\Models\SysStdRefundItem::class, 'refund_id', 'refund_id');
    }

    /**
     * 退单属于订单
     * trade
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function trade()
    {
        return $this->belongsTo(\App\Models\SysStdTrade::class, 'tid', 'tid');
    }

}