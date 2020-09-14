<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 退款申请 包括退货时，卖家同意退货
 *
 * Class RpReturnGoodsAgreeRequest
 *
 * @package App\Services\Platform\Taobao\Client\Top\Request
 * @method $this setRefundId($value)
 * @method $this setName($value)
 * @method $this setAddress($value)
 * @method $this setPost($value)
 * @method $this setTel($value)
 * @method $this setMobile($value)
 * @method $this setRemark($value)
 * @method $this setRefundPhase($value)
 * @method $this setRefundVersion($value)
 * @method $this setSellerAddressId($value)
 * @method $this setPostFeeBearRole($value)
 * @method $this setVirtualReturnGoods($value)
 * @method $this setSellerIp($value)
 *
 * @author linqihai
 * @since 2020/1/2 10:00
 */
class RpReturnGoodsAgreeRequest extends TopRequest
{
    protected $apiName = 'taobao.rp.returngoods.agree';

    protected $paramKeys    = [
        'refundId',
        'name',
        'address',
        'post',
        'tel',
        'mobile',
        'remark',
        'refundPhase',
        'refundVersion',
        'sellerAddressId',
        'postFeeBearRole',
        'virtualReturnGoods',
    ];

    public function check()
    {
    }
}