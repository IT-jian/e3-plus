<?php


namespace App\Services\Platform\Jingdong\Client\Jos\Request;


use App\Services\Platform\Jingdong\Client\Jos\JosRequest;


/**
 * 查询退款申请列表
 *
 * Class RefundApplyQueryPageListRequest
 *
 * @package App\Services\Platform\Jingdong\Client\Jos\Request
 *
 * @method $this setIds($value)	用户pin
 * @method $this setStatus($value)	0、未审核 1、审核通过2、审核不通过 3、京东财务审核通过 4、京东财务审核不通过 5、人工审核通过 6、拦截并退款 7、青龙拦截成功 8、青龙拦截失败 9、强制关单并退款 10、物流待跟进 11、用户撤销。不传是查询全部状态
 * @method $this setOrderId($value)	订单ID
 * @method $this setBuyerId($value)	客户帐号
 * @method $this setBuyerName($value)	客户姓名
 * @method $this setApplyTimeStart($value)	申请日期（start）格式类型（yyyy-MM-dd hh:mm:ss,2013-08-06 21:02:07）
 * @method $this setApplyTimeEnd($value)	申请结束时间
 * @method $this setCheckTimeStart($value)	审核日期（start）格式类型（yyyy-MM-dd hh:mm:ss,2013-08-06 21:02:07）
 * @method $this setCheckTimeEnd($value)	审核结束时间
 * @method $this setPageIndex($value)		页码(显示多少页，区间为1-100)
 * @method $this setPageSize($value)	每页显示多少条（区间为1-50）
 * @method $this setStoreId($value)  门店ID
 *
 * jingdong_presale_order_updateOrder_getPresaleOrderByPage_response.returnType.data
 */
class RefundApplyQueryPageListRequest extends JosRequest
{
    /**
     * 接口名称
     *
     * @var string
     * @see https://open.jd.com/home/home#/doc/api?apiCateId=71&apiId=925&apiName=jingdong.pop.afs.soa.refundapply.queryPageList
     */
    protected $apiName = 'jingdong.pop.afs.soa.refundapply.queryPageList';

    protected $paramKeys = [
        'ids',
        'status',
        'orderId',
        'buyerId',
        'buyerName',
        'applyTimeStart',
        'applyTimeEnd',
        'checkTimeStart',
        'checkTimeEnd',
        'pageIndex',
        'pageSize',
        'storeId',
    ];

    protected $commaSeparatedParams = ['ids'];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
    }
}