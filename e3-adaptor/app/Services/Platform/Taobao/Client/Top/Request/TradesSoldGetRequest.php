<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 查询卖家已卖出的交易数据（根据创建时间）
 *
 * Class TradesSoldGetRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setFields($value)
 * @method $this setStartCreated($value)
 * @method $this setEndCreated($value)
 * @method $this setStatus($value)
 * @method $this setBuyerNick($value)
 * @method $this setType($value)
 * @method $this setExtType($value)
 * @method $this setRateStatus($value)
 * @method $this setTag($value)
 * @method $this setPageNo($value)
 * @method $this setPageSize($value)
 * @method $this setUseHasNext($value)
 * @method $this setBuyerOpenId($value)
 *
 * @author linqihai
 * @since 2020/7/18 21:07
 * @see https://open.taobao.com/api.htm?docId=46&docType=2
 */
class TradesSoldGetRequest extends TopRequest
{
    protected $apiName = 'taobao.trades.sold.get';

    protected $commaSeparatedParams = [
        'fields',
    ];

    /**
     * 入参字段
     * @var array
     */
    protected $paramKeys = [
        'fields',
        'startCreated',
        'endCreated',
        'status',
        'buyerNick',
        'type',
        'extType',
        'rateStatus',
        'tag',
        'pageNo',
        'pageSize',
        'useHasNext',
        'buyerOpenId',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
    }
}
