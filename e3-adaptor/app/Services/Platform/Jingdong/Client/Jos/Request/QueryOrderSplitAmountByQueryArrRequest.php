<?php


namespace App\Services\Platform\Jingdong\Client\Jos\Request;


use App\Services\Platform\Jingdong\Client\Jos\JosRequest;


/**
 * 订单金额查询服务 -- 根据查询条件查询指定条件对应的赠品无价值订单金额
 *  按照对应查询条件返回指定类型金额明细信息，0元赠品价格按0元计算，查询出来的数据和订单、发票的统计口径保持一致
 *
 * Class AscCommonCancelRequest
 *
 * @package App\Services\Platform\Jingdong\Client\Jos\Request
 *
 * @method $this setId($orderId) 京东订单编号
 * @method $this setSystemName($value) 访问系统名称（邮件分配）
 * @method $this setSystemKey($value) 访问系统key（邮件分配））
 * @method $this setQueryTypes($value) all(全部金额信息),coupon(优惠券分摊金额信息)
 *
 * jingdong_queryOrderSplitAmountByQueryArr_response.orderSplitAmountResult
 */
class QueryOrderSplitAmountByQueryArrRequest extends JosRequest
{
    /**
     * 接口名称
     *
     * @var string
     * @see http://open.jd.com/home/home#/doc/api?apiCateId=250&apiId=2402&apiName=jingdong.queryOrderSplitAmountByQueryArr

     */
    protected $apiName = 'jingdong.queryOrderSplitAmountByQueryArr';

    protected $paramKeys = [
        'id',
        'systemName',
        'systemKey',
        'queryTypes',
    ];

    protected $defaultParamValues = [];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->id, "id");
        //RequestCheckUtil::checkNotNull($this->systemName, "systemName");
        //RequestCheckUtil::checkNotNull($this->systemKey, "systemKey");
        //RequestCheckUtil::checkNotNull($this->queryTypes, "queryTypes");
    }
}