<?php


namespace App\Services\Platform\Jingdong\Client\Jos\Request;


use App\Services\Platform\Jingdong\Client\Jos\JosRequest;


/**
 * AG取消发货回写--商家审核退款单
 *
 * Class PopAfsSoaRefundapplyReplyRefundRequest
 *
 * @package App\Services\Platform\Jingdong\Client\Jos\Request
 *
 * @method $this setStatus($status)  退款申请单状态 1审核通过 2审核不通过 6通过并京东拦截 9强制关单 10物流待跟进 
 * @method $this setId($id)  退款单id 
 * @method $this setCheckUserName($checkUserName)  审核人 
 * @method $this setRemark($remark)  审核备注,除强制关单外的其它审核状态下必填 
 * @method $this setRejectType($rejectType)  仅在审核不通过时填写该值。1：（支持拒收）商品已在配送途中，无法取消；2：商品已签收，无法取消；3：国际站保税区订单，已报关；4：已电话沟通客户，客户同意签收商品；5：其他；6：（不支持拒收）商品已在配送途中，无法取消 
 * @method $this setOutWareStatus($outWareStatus)  实际是否已出库:1已出库 2未出库 
 *
 * jingdong_pop_afs_soa_refundapply_replyRefund_response.replyResult.success
 */
class PopAfsSoaRefundapplyReplyRefundRequest extends JosRequest
{
    /**
     * 接口名称
     *
     * @var string
     * @see https://open.jd.com/home/home#/doc/api?apiCateId=71&apiId=923&apiName=jingdong.pop.afs.soa.refundapply.replyRefund
     */
    protected $apiName = 'jingdong.pop.afs.soa.refundapply.replyRefund';

    protected $paramKeys = [
        'status',
        'id',
        'checkUserName',
        'remark',
        'rejectType',
        'outWareStatus',
    ];


    protected $defaultParamValues = [
        'checkUserName' => 'adaptor',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->status, "status");
        //RequestCheckUtil::checkNotNull($this->id, "id");
        //RequestCheckUtil::checkNotNull($this->checkUserName, "checkUserName");
    }
}
