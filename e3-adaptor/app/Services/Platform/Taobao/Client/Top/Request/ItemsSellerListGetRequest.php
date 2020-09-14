<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 批量查询商品详情
 *
 * Class ItemsSellerListGetRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setFields($value) 需要返回的商品字段列表。可选值：点击返回结果中的Item结构体中能展示出来的所有字段，多个字段用“,”分隔。注：返回所有sku信息的字段名称是sku而不是skus。
 * @method $this setNumIids($value) 商品ID列表，多个ID用半角逗号隔开，一次最多不超过20个。注：获取不存在的商品ID或获取别人的商品都不会报错，但没有商品数据返回。
 *
 * items_seller_list_get_response.items
 */
class ItemsSellerListGetRequest extends TopRequest
{
    protected $apiName = 'taobao.item.seller.get';

    /**
     * 逗号分隔的字段
     *
     * @var array
     */
    protected $commaSeparatedParams = [
        'fields',
        'numIids',
    ];

    /**
     * 入参字段
     * @var array
     */
    protected $paramKeys    = [
        'fields',
        'numIids',
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
        //RequestCheckUtil::checkNotNull($this->numIids, "num_iids");
    }
}
