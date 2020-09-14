<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 查询卖家收到的退款列表
 *
 * Class RefundsReceiveGetRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setFields($value)
 * @method $this setStatus($value)
 * @method $this setSellerNick($value)
 * @method $this setType($value)
 * @method $this setStartModified($value)
 * @method $this setEndModified($value)
 * @method $this setPageNo($value)
 * @method $this setPageSize($value)
 * @method $this setUseHasNext($value)
 *
 * @author linqihai
 * @since 2020/7/19 11:07
 * @see https://open.taobao.com/api.htm?spm=a219a.7386653.0.0.4617669a48SmSr&source=search&docId=52&docType=2
 *
 * refunds_receive_get_response.refunds.refund
 */
class RefundsReceiveGetRequest extends TopRequest
{
    protected $apiName = 'taobao.refunds.receive.get';

    protected $commaSeparatedParams = [
        'fields',
    ];
    protected $defaultParamValues = [
        'fields' => 'refund_id,tid,title,buyer_nick,seller_nick,total_fee,status,created,refund_fee,oid,good_status,company_name,sid,payment,reason,desc,has_good_return,modified,order_status,refund_phase',
    ];

    /**
     * 入参字段
     * @var array
     */
    protected $paramKeys = [
        'fields',
        'status',
        'sellerNick',
        'type',
        'pageNo',
        'pageSize',
        'startModified',
        'endModified',
        'useHasNext',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
    }
}
