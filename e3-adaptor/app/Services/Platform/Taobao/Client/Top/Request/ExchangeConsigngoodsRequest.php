<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 换货单--卖家发货
 *
 * Class ExchangeConsigngoodsRequest
 *
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setDisputeId($value)
 * @method $this setLogisticsNo($value)
 * @method $this setLogisticsType($value)
 * @method $this setLogisticsCompanyName($value)
 * @method $this setFields($value)
 *
 * tmall_exchange_consigngoods_response.result.success
 *
 * @author linqihai
 * @since 2020/05/22 10:32
 */
class ExchangeConsigngoodsRequest extends TopRequest
{
    /**
     * 接口名称
     *
     * @var string
     * @see https://open.taobao.com/api.htm?spm=a219a.7386797.0.0.4afc669aOKdRAr&source=search&docId=10690&docType=2
     */
    protected $apiName = 'tmall.exchange.consigngoods';

    protected $commaSeparatedParams = [
        'fields',
    ];

    protected $defaultParamValues = [
        'fields' => 'dispute_id, bizorder_id, status, modified'
    ];

    protected $paramKeys = [
        'disputeId',
        'logisticsNo',
        'logisticsType',
        'logisticsCompanyName',
        'fields',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
    }
}