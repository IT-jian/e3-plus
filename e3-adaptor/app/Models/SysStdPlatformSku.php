<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class SysStdPlatformSku
 * @package App\Models
 * @version December 23, 2019, 10:45 am CST
 *
 * @property string platform
 * @property string shop_code
 * @property unsignedBigInteger sku_id
 * @property unsignedBigInteger goods_id
 * @property string goods_name
 * @property unsignedBigInteger outer_id
 * @property integer num
 * @property decimal price
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class SysStdPlatformSku extends Model
{
    use PlatformTrait;

    protected $table = 'sys_std_platform_sku';

    protected $primaryKey = 'sku_id';

    public $incrementing = false;

    protected $fillable = [
        'platform',
        'shop_code',
        'sku_id',
        'num_iid',
        'title',
        'outer_iid',
        'barcode',
        'approve_status',
        'color',
        'size',
        'outer_id',
        'quantity',
        'is_delete',
        'price',
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
        'platform'       => 'string',
        'shop_code'      => 'string',
        'title'          => 'string',
        'sku_id'         => 'string',
        'num_iid'        => 'string',
        'outer_id'       => 'string',
        'outer_iid'      => 'string',
        'approve_status' => 'string',
        'color'          => 'string',
        'size'           => 'string',
        'quantity'       => 'integer',
        'created'        => 'datetime',
        'modified'       => 'datetime',
        'price'       => 'decimal:2',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    public function mapBySkuId($platform, $shopCode, $skuId)
    {
        $where = [
            'outer_id' => $skuId,
            'shop_code' => $shopCode,
            'platform' => $platform,
        ];
        $platformSku = $this->where($where)->first();

        return $platformSku;
    }

    public function mapByOuterId($platform, $shopCode, $outerId)
    {
        $where = [
            'sku_id' => $outerId,
            'shop_code' => $shopCode,
            'platform' => $platform,
        ];
        $platformSku = $this->where($where)->first();

        return $platformSku;
    }
}
