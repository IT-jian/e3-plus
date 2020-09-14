<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class SysStdRefundItem
 * @package App\Models
 * @version December 22, 2019, 9:13 pm CST
 *
 * @property unsignedBigInteger refund_id
 * @property string outer_iid
 * @property unsignedInteger num
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class SysStdRefundItem extends Model
{
    use PlatformTrait;

    protected $table = 'sys_std_refund_item';

    protected $primaryKey = 'refund_id';

    public $incrementing = false;

    protected $fillable = [
        'refund_id',
        'row_index',
        'sku_id',
        'outer_iid',
        'outer_sku_id',
        'num_iid',
        'num',
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
        'outer_iid' => 'string',
        'sku_id' => 'string',
        'outer_sku_id' => 'string',
        'num_iid' => 'string',
        'refund_id' => 'string',
        'reason' => 'string',
        'desc' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

}