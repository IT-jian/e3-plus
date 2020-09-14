<?php

namespace App\Models;

use App\Models\BaseModel as Model;

/**
 * Class JingdongTrade
 * @package App\Models
 * @version April 29, 2020, 10:43 pm CST
 *
 * @property unsignedBigInteger order_id
 * @property string vender_id
 * // 1）WAIT_SELLER_STOCK_OUT 等待出库 2）WAIT_GOODS_RECEIVE_CONFIRM 等待确认收货 3）WAIT_SELLER_DELIVERY 等待发货（只适用于海外购商家，含义为“等待境内发货”标签下的订单,非海外购商家无需使用）
 * 4) PAUSE 暂停（loc订单可通过此状态获取） 5）FINISHED_L 完成 6）TRADE_CANCELED 取消 7）LOCKED 已锁定 8）POP_ORDER_PAUSE pop业务暂停，如3c号卡/履约/黄金的业务
 * @property string state
 * @property string order_type
 * @property unsignedInteger created
 * @property string|\Carbon\Carbon modified
 * @property mediumText origin_content
 * @property unsignedInteger origin_created
 * @property unsignedInteger origin_modified
 * @property unsignedBigInteger version
 */
class JingdongTrade extends Model
{
    protected $table = 'jingdong_trade';

    protected $primaryKey = 'order_id';

    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'vender_id',
        'state',
        'order_type',
        'direct_parent_order_id',
        'parent_order_id',
        'created',
        'modified',
        'origin_content',
        'origin_created',
        'origin_modified',
        'version',
        'sync_status'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'order_id' => 'string',
        'parent_order_id' => 'string',
        'direct_parent_order_id' => 'string',
        'vender_id' => 'string',
        'state' => 'string',
        'order_type' => 'string',
        'origin_content' => 'array',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

}
