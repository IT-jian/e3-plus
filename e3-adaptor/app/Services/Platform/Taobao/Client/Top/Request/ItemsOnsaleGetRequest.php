<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * taobao.items.onsale.get( 获取当前会话用户出售中的商品列表 )
 *
 * Class ItemsOnsaleGetRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setFields($value) 需返回的字段列表 approve_status,num_iid,title,nick,type,cid,pic_url,num,props,valid_thru,list_time,price,has_discount,has_invoice,has_warranty,has_showcase,modified,delist_time,postage_id,seller_cids,outer_id,sold_quantity
 * @method $this setQ($value)  搜索字段
 * @method $this setCid($value)  商品类目ID
 * @method $this setSellerCids($value)  卖家店铺内自定义类目ID
 * @method $this setPageNo($value)  页码 页码。取值范围:大于零的整数。默认值为1,即默认返回第一页数据。用此接口获取数据时，当翻页获取的条数（page_no*page_size）超过10万,为了保护后台搜索引擎，接口将报错
 * @method $this setHasDiscount($value)  是否参与会员折扣
 * @method $this setHasShowcase($value)  是否橱窗推荐
 * @method $this setOrderBy($value)  排序方式 格式为column:asc/desc ，column可选值:list_time(上架时间),delist_time(下架时间),num(商品数量)，modified(最近修改时间)，sold_quantity（商品销量）,;默认上架时间降序(即最新上架排在前面)。如按照上架时间降序排序方式为list_time:desc
 * @method $this setIsTaobao($value)  商品是否在淘宝显示
 * @method $this setIsEx($value)  商品是否在外部网店显示
 * @method $this setPageSize($value)  每页条数 取值范围:大于零的整数;最大值：200；默认值：40
 * @method $this setStartModified($value) 起始的修改时间
 * @method $this setEndModified($value) 结束的修改时间
 * @method $this setIsCspu($value)  是否挂接了达尔文标准产品体系
 * @method $this setIsCombine($value)  组合商品
 * @method $this setAuctionType($value) 商品类型：a-拍卖,b-一口价
 *
 * @author linqihai
 * @since 2020/5/27 18:29
 *
 * items_onsale_get_response.items
 */
class ItemsOnsaleGetRequest extends TopRequest
{
    protected $apiName = 'taobao.items.onsale.get';

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
    protected $paramKeys = [
        'fields',
        'q',
        'cid',
        'sellerCids',
        'pageNo',
        'hasDiscount',
        'hasShowcase',
        'orderBy',
        'isTaobao',
        'isEx',
        'pageSize',
        'startModified',
        'endModified',
        'isCspu',
        'isCombine',
        'auctionType',
    ];

    /**
     * 默认值字段
     *
     * @var array
     */
    protected $defaultParamValues = [
        'fields' => 'approve_status,num_iid,title,nick,type,cid,pic_url,num,props,valid_thru,list_time,price,has_discount,has_invoice,has_warranty,has_showcase,modified,delist_time,postage_id,seller_cids,outer_id,sold_quantity',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
    }
}
