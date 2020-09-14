<?php


namespace App\Services\Platform\Jingdong\Client\Jos\Request;


use App\Services\Platform\Jingdong\Client\Jos\JosRequest;


/**
 * 查看售后和退款信息
 *
 * Class AscServiceAndRefundViewRequest
 *
 * @package App\Services\Platform\Jingdong\Client\Jos\Request
 *
 * @method $this setOrderId($value) 订单号 
 * @method $this setApplyTimeBegin($value) 申请时间开始 
 * @method $this setApplyTimeEnd($value) 申请时间结束 
 * @method $this setApproveTimeBegin($value) 申请时间开始 
 * @method $this setApproveTimeEnd($value) 申请时间结束 
 * @method $this setPageNumber($value) 页码(从1开始) 
 * @method $this setPageSize($value) 每页大小（1-50，默认10） 
 * @method $this setExtJsonStr($value) 扩展条件（JSON格式） 

 *
 * jingdong_asc_serviceAndRefund_view_responce.pageResult.data
 */
class AscServiceAndRefundViewRequest extends JosRequest
{
    /**
     * 接口名称
     *
     * @var string
     * @see https://open.jd.com/home/home#/doc/api?apiCateId=241&apiId=3405&apiName=jingdong.asc.serviceAndRefund.view
     */
    protected $apiName = 'jingdong.asc.serviceAndRefund.view';

    protected $paramKeys = [
        'orderId',
        'applyTimeBegin',
        'applyTimeEnd',
        'approveTimeBegin',
        'approveTimeEnd',
        'pageNumber',
        'pageSize',
        'extJsonStr',
    ];

    public function check()
    {

    }
}