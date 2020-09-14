<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class JingdongSku
 * @package App\Models
 * @version June 7, 2020, 9:38 pm CST
 *
 * @property unsignedBigInteger sku_id
 * @property string vender_id
 * @property string shop_code
 * @property string sku_name
 * @property string outer_id
 * @property string barcode
 * @property string ware_id
 * @property string ware_title
 * @property string category_id
 * @property tinyint status
 * @property decimal jd_price
 * @property json origin_content
 * @property timestamp created
 * @property timestamp modified
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class JingdongSku extends Model
{
    protected $table = 'jingdong_sku';

    protected $primaryKey = 'sku_id';

    public $incrementing = false;

    protected $fillable = [
        'sku_id',
        'vender_id',
        'shop_code',
        'sku_name',
        'outer_id',
        'barcode',
        'ware_id',
        'ware_title',
        'category_id',
        'status',
        'jd_price',
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
        'vender_id' => 'string',
        'shop_code' => 'string',
        'sku_name' => 'string',
        'outer_id' => 'string',
        'barcode' => 'string',
        'ware_id' => 'string',
        'ware_title' => 'string',
        'category_id' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];
}
