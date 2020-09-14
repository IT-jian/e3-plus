<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class SysStdTradeItem
 * @package App\Models
 * @version December 19, 2019, 4:19 pm CST
 *
 * @property unsignedInteger tid
 * @property unsignedInteger oid
 * @property string num_iid
 * @property string outer_iid
 * @property string outer_sku_id
 * @property integer num
 * @property decimal total_fee
 * @property decimal discount_fee
 * @property decimal adjust_fee
 * @property decimal payment
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class SysStdTradeItem extends Model
{
    protected $table = 'sys_std_trade_item';

    protected $primaryKey = 'tid';

    public $incrementing = false;

    protected $fillable = [
        'tid',
        'platform',
        'oid',
        'row_index',
        'title',
        'color',
        'size',
        'divide_order_fee',
        'sku_id',
        'num_iid',
        'outer_iid',
        'outer_sku_id',
        'num',
        'total_fee',
        'discount_fee',
        'part_mjz_discount',
        'adjust_fee',
        'payment',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'platform'     => 'string',
        'row_index'    => 'string',
        'oid'          => 'string',
        'tid'          => 'string',
        'sku_id'       => 'string',
        'num_iid'      => 'string',
        'outer_iid'    => 'string',
        'outer_sku_id' => 'string',
        'num'          => 'integer',

        'title' => 'string',
        'color' => 'string',
        'size'  => 'string',

        'total_fee'         => 'decimal:2',
        'part_mjz_discount' => 'decimal:2',
        'adjust_fee'        => 'decimal:2',
        'payment'           => 'decimal:2',
        'divide_order_fee'  => 'decimal:2',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

}