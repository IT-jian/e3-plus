<?php


namespace App\Services\Platform\Jingdong\Client\Jos\Request;


use App\Services\Platform\Jingdong\Client\Jos\JosRequest;


/**
 * 增量查询服务单信息
 *
 * Class AscReceiveListRequest
 *
 * @package App\Services\Platform\Jingdong\Client\Jos\Request
 *
 * @method $this setBuId($value) 商家编号 
 * @method $this setOperatePin($value) 操作人账号 
 * @method $this setOperateNick($value) 操作人姓名 
 * @method $this setServiceId($value) 服务单号 
 * @method $this setOrderId($value) 订单号 
 * @method $this setServiceStatus($value) 服务单状态 
 * @method $this setOrderType($value) 订单类型 
 * @method $this setUpdateTimeBegin($value) 服务单更新时间开始（与服务单更新时间结束成对必填） 
 * @method $this setUpdateTimeEnd($value) 服务单更新时间结束（与服务单更新时间开始成对必填） 
 * @method $this setFreightUpdateDateBegin($value) 运单更新时间开始（与运单更新时间结束成对必填） 
 * @method $this setFreightUpdateDateEnd($value) 运单更新时间结束（与运单更新时间开始成对必填） 
 * @method $this setPageNumber($value) 页码(从1开始) 
 * @method $this setPageSize($value) 每页大小（1\\\\\x7e50，默认10） 
 * @method $this setExtJsonStr($value) 扩展条件（JSON格式） 

 *
 * jingdong_asc_sync_list_response.pageResult.data
 */
class AscSyncListRequest extends JosRequest
{
    /**
     * 接口名称
     *
     * @var string
     * @see https://jos.jd.com/api/detail.htm?apiName=jingdong.asc.receive.list&id=2114
     */
    protected $apiName = 'jingdong.asc.sync.list';

    protected $paramKeys = [
        'buId',
        'operatePin',
        'operateNick',
        'serviceId',
        'orderId',
        'serviceStatus',
        'orderType',
        'updateTimeBegin',
        'updateTimeEnd',
        'freightUpdateDateBegin',
        'freightUpdateDateEnd',
        'pageNumber',
        'pageSize',
        'extJsonStr',
    ];

    protected $defaultParamValues = [
        'operatePin' => 'adaptor',
        'operateNick' => 'adaptor',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->buId, "buId");
        //RequestCheckUtil::checkNotNull($this->operatePin, "operatePin");
        //RequestCheckUtil::checkNotNull($this->operateNick, "operateNick");
    }
}