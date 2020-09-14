<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 查询商品详情
 *
 * Class ItemSellerGetRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setFields($value)
 * @method $this setNumIid($value)
 *
 * @author linqihai
 * @since 2020/5/20 18:29
 * item_seller_get_response.item
 */
class ItemSellerGetRequest extends TopRequest
{
    protected $apiName = 'taobao.item.seller.get';

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
        'numIid',
    ];

    /**
     * 默认值字段
     *
     * @var array
     */
    protected $defaultParamValues = [
        'fields' => 'num_iid,title,outer_id',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
        //RequestCheckUtil::checkNotNull($this->numIid, "num_iid");
    }
}