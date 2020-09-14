<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 查询买家申请的退款列表
 *
 * Class RefundsApplyGetRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setFields($value)
 * @method $this setTid($value)
 * @method $this setStatus($value)
 * @method $this setSellerNick($value)
 * @method $this setType($value)
 * @method $this setPageNo($value)
 * @method $this setPageSize($value)
 *
 * @author linqihai
 * @since 2020/7/19 11:07
 * @see https://open.taobao.com/api.htm?docId=51&docType=2
 *
 * refunds_apply_get_response.refunds.refund
 */
class RefundsApplyGetRequest extends TopRequest
{
    protected $apiName = 'taobao.refunds.apply.get';

    protected $commaSeparatedParams = [
        'fields',
    ];
    protected $defaultParamValues = [
        'fields' => 'refund_id,tid,title,buyer_nick,seller_nick,total_fee,status,created,refund_fee',
    ];

    /**
     * 入参字段
     * @var array
     */
    protected $paramKeys = [
        'fields',
        'tid',
        'status',
        'sellerNick',
        'type',
        'pageNo',
        'pageSize',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
    }
}
