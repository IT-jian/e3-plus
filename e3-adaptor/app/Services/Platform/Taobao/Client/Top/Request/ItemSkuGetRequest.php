<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 查询sku详情
 *
 * Class ExchangeReturnGoodsRefuseRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setSkuId($value)
 * @method $this setFields($value)
 * @method $this setNumIid($value)
 *
 * @author linqihai
 * @since 2020/5/20 18:29
 */
class ItemSkuGetRequest extends TopRequest
{
    protected $apiName = 'taobao.item.sku.get';

    /**
     * 逗号分隔的字段
     *
     * @var array
     */
    protected $commaSeparatedParams = [
        'fields',
    ];

    /**
     * 入参字段
     * @var array
     */
    protected $paramKeys    = [
        'fields',
        'skuId',
        'numIid',
    ];

    /**
     * 默认值字段
     *
     * @var array
     */
    protected $defaultParamValues = [
        'fields' => 'sku_id,outer_id,properties_name,quantity,modified',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
        //RequestCheckUtil::checkNotNull($this->skuId, "sku_id");
    }
}
