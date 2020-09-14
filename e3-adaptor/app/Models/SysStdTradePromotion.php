<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class SysStdTradePromotion
 * @package App\Models
 * @version December 19, 2019, 4:19 pm CST
 *
 * @property unsignedInteger id
 * @property string promotion_name
 * @property decimal discount_fee
 * @property string gift_item_id
 * @property integer gift_item_num
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class SysStdTradePromotion extends Model
{
    protected $table = 'sys_std_trade_promotion';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $fillable = [
        'platform',
        'tid',
        'id',
        'promotion_id',
        'promotion_name',
        'promotion_desc',
        'discount_fee',
        'gift_item_id',
        'gift_item_num',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'promotion_id'   => 'string',
        'promotion_name' => 'string',
        'promotion_desc' => 'string',
        'discount_fee'   => 'decimal:2',
        'tid'            => 'string',
        'id'             => 'string',
        'gift_item_id'   => 'string',
        'gift_item_num'  => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

}