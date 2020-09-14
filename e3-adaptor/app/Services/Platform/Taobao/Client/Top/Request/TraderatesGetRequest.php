<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 天猫评论
 *
 * Class TraderatesGetRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setFields($value)	需返回的字段列表。可选值：TradeRate 结构中的所有字段，多个字段之间用“,”分隔
 * @method $this setRateType($value)	评价类型。可选值:get(得到),give(给出)
 * @method $this setRole($value)	评价者角色即评价的发起方。可选值:seller(卖家),buyer(买家)。 当 give buyer 以买家身份给卖家的评价； 当 get seller 以买家身份得到卖家给的评价； 当 give seller 以卖家身份给买家的评价； 当 get buyer 以卖家身份得到买家给的评价。
 * @method $this setResult($value)	评价结果。可选值:good(好评),neutral(中评),bad(差评)
 * @method $this setPageNo($value)	页码。取值范围:大于零的整数最大限制为200; 默认值:1
 * @method $this setPageSize($value)	每页获取条数。默认值40，最小值1，最大值150。
 * @method $this setStartDate($value)	评价开始时。如果只输入开始时间，那么能返回开始时间之后的评价数据。
 * @method $this setEndDate($value)	评价结束时间。如果只输入结束时间，那么全部返回所有评价数据。
 * @method $this setTid($value)	交易订单id，可以是父订单id号，也可以是子订单id号
 * @method $this setUseHasNext($value)	是否启用has_next的分页方式，如果指定true,则返回的结果中不包含总记录数，但是会新增一个是否存在下一页的的字段，通过此种方式获取评价信息，效率在原有的基础上有80%的提升。
 * @method $this setNumIid($value)	商品的数字ID

 */
class TraderatesGetRequest extends TopRequest
{
    protected $apiName = 'taobao.traderates.get';

    /**
     * 入参字段
     * @var array
     */
    protected $paramKeys = [
        'fields',
        'rateType',
        'role',
        'result',
        'pageNo',
        'pageSize',
        'startDate',
        'endDate',
        'tid',
        'useHasNext',
        'numIid',
    ];

    protected $commaSeparatedParams = ['fields'];

    protected $defaultParamValues = [
        'fields'    => 'tid, oid, role, nick, result, created, rated_nick, item_title, item_price, content, reply, num_iid, valid_score',
        'rateType'  => 'get',
        'role' => 'buyer',
        'useHasNext' => 'true'
    ];
    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
        //RequestCheckUtil::checkNotNull($this->rateType, "rate_type");
        //RequestCheckUtil::checkNotNull($this->rate_type, "rate_type");
    }
}
