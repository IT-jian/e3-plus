<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use Illuminate\Support\Facades\Redis;

/**
 * Class TaobaoSkusQuantityUpdateQueue
 * @package App\Models
 * @version August 17, 2020, 10:24 am CST
 *
 * @property unsignedBigInteger id
 * @property unsignedBigInteger num_iid
 * @property unsignedBigInteger sku_id
 * @property tinyint update_type
 * @property unsignedBigInteger sku_version
 * @property unsignedBigInteger batch_version
 * @property tinyint status
 * @property int try_times
 * @property string shop_code
 * @property string outer_id
 * @property json message
 * @property timestamp start_at
 * @property timestamp end_at
 * @property timestamp created_at
 * @property timestamp updated_at
 */
class TaobaoSkusQuantityUpdateQueue extends Model
{
    protected $table = 'taobao_skus_quantity_update_queue';

    protected $fillable = [
        'id',
        'num_iid',
        'sku_id',
        'outer_id',
        'shop_code',
        'quantity',
        'update_type',
        'sku_version',
        'batch_version',
        'try_times',
        'status',
        'message',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'num_iid'       => 'string',
        'sku_id'        => 'string',
        'outer_id'      => 'string',
        'sku_version'   => 'string',
        'batch_version' => 'string',
        'message'       => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    /**
     * 平台限速了，则打上标识
     *
     * @return mixed
     */
    protected function setLimitedByPlatform()
    {
        return Redis::set("taobao_sku_request_limited", 1, "EX", 60, "NX");
    }

    /**
     * 判断是否有平台限速标识
     *
     * @return bool
     */
    protected function isLimitedByPlatform()
    {
        return 1 == Redis::get("taobao_sku_request_limited") ? true : false;
    }
}
