<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class SysStdExchangeItem
 * @package App\Models
 * @version December 24, 2019, 2:14 pm CST
 *
 * @property unsignedBigInteger dispute_id
 * @property string platform
 * @property string goods_status
 * @property unsignedBigInteger bought_sku
 * @property unsignedBigInteger exchange_sku
 * @property unsignedInteger num
 * @property string reason
 * @property string desc
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class SysStdExchangeItem extends Model
{
    use PlatformTrait;

    protected $table = 'sys_std_exchange_item';

    protected $primaryKey = 'dispute_id';

    public $incrementing = false;

    protected $fillable = [
        'dispute_id',
        'oid',
        'row_index',
        'platform',
        'goods_status',
        'bought_sku',
        'bought_num_iid',
        'bought_outer_iid',
        'bought_outer_sku_id',
        'bought_color',
        'bought_size',
        'exchange_title',
        'exchange_num_iid',
        'exchange_outer_iid',
        'exchange_outer_sku_id',
        'exchange_color',
        'exchange_size',
        'exchange_sku',
        'num',
        'price',
        'reason',
        'desc',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'oid'                   => 'string',
        'row_index'             => 'string',
        'bought_sku'            => 'string',
        'bought_num_iid'        => 'string',
        'bought_outer_iid'      => 'string',
        'bought_outer_sku_id'   => 'string',
        'bought_color'          => 'string',
        'bought_size'           => 'string',
        'exchange_title'        => 'string',
        'exchange_num_iid'      => 'string',
        'exchange_outer_iid'    => 'string',
        'exchange_outer_sku_id' => 'string',
        'exchange_color'        => 'string',
        'exchange_size'         => 'string',
        'exchange_sku'          => 'string',
        'platform'              => 'string',
        'goods_status'          => 'string',
        'reason'                => 'string',
        'desc'                  => 'string',

        'price' => 'decimal:2',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

}