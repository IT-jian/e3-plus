<?php


namespace App\Services\Platform\Jingdong\Client\Jos\Request;


use App\Services\Platform\Jingdong\Client\Jos\JosRequest;

/**
 * Class WareReadSearchWare4ValidRequest
 * @package App\Services\Platform\Jingdong\Client\Jos\Request
 *
 *
 * @method $this setWareId($value) 商品id列表,最多20个
 * @method $this setSearchKey($value) 商品搜索关键词,需要配合搜索域实searchField现
 * @method $this setSearchField($value) 商品搜索域的范围,默认是商品名称.目前值范围[title]
 * @method $this setCategoryId($value) 商品3级类目
 * @method $this setShopCategoryIdLevel1($value) 店内分类的父ID，如果店内分类只设置了一级，shopCategoryIdLevel1需要传0或不传，shopCategoryIdLevel2传一级店内分类
 * @method $this setShopCategoryIdLevel2($value) 店内分类子id
 * @method $this setTemplateId($value) 关联板式ID 关联版式ID 通过接口 jingdong.template.read.findTemplatesByVenderId 获取
 * @method $this setPromiseId($value) 时效模板ID
 * @method $this setBrandId($value) 已经授权过的品牌ID(通过商家授权类目接口获取)
 * @method $this setFeatureKey($value) 商品的特殊属性key
 * @method $this setFeatureValue($value) 商品的特殊属性value
 * @method $this setWareStatusValue($value)	商品状态,多个值属于[或]操作 1:从未上架 2:自主下架 4:系统下架 8:上架 513:从未上架待审 514:自主下架待审 516:系统下架待审 520:上架待审核 1028:系统下架审核失败
 * @method $this setItemNum($value) itemNum 商品货号
 * @method $this setBarCode($value) barCode 商品的条形码.UPC码,SN码,PLU码统称为条形码
 * @method $this setColType($value) colType 合作类型 商家接口获取
 * @method $this setStartCreatedTime($value) 开始创建时间
 * @method $this setEndCreatedTime($value) 结束创建时间
 * @method $this setStartJdPrice($value) 开始京东价
 * @method $this setEndJdPrice($value) 结束京东价
 * @method $this setStartOnlineTime($value) 开始上架时间
 * @method $this setEndOnlineTime($value) 结束上架时间
 * @method $this setStartModifiedTime($value) 开始修改时间
 * @method $this setEndModifiedTime($value) 结束修改时间
 * @method $this setStartOfflineTime($value) 开始下架时间
 * @method $this setEndOfflineTime($value) 结束下架时间
 * @method $this setStartStockNum($value) 开始商品库存
 * @method $this setEndStockNum($value) 结束商品库存
 * @method $this setOrderField($value) 排序字段.值范围[wareId offlineTime onlineTime stockNum jdPrice modified]
 * @method $this setOrderType($value) 排序方式.值范围[asc desc]
 * @method $this setPageNo($value) 页码 第一页开始
 * @method $this setPageSize($value) 每页条数
 * @method $this setTransportId($value) 运费模板ID
 * @method $this setClaim($value) 是否认领
 * @method $this setGroupId($value) 分组ID(供销)
 * @method $this setMultiCategoryId($value) 	末级类目ID
 * @method $this setWarePropKey($value) 商品的类目属性key
 * @method $this setWarePropValue($value) 商品的类目属性value
 * @method $this setField($value) 可选的返回的字段如 ：[wareId offlineTime onlineTime stockNum jdPrice modified]
 */
class WareReadSearchWare4ValidRequest extends JosRequest
{
    /**
     * @var string
     * @see https://open.jd.com/home/home#/doc/api?apiCateId=48&apiId=1587&apiName=jingdong.ware.read.searchWare4Valid
     */
    protected $apiName = 'jingdong.ware.read.searchWare4Valid';

    /**
     * 入参字段
     * @var array
     */
    protected $paramKeys = [
        'wareId',
        'searchKey',
        'searchField',
        'categoryId',
        'shopCategoryIdLevel1',
        'shopCategoryIdLevel2',
        'templateId',
        'promiseId',
        'brandId',
        'featureKey',
        'featureValue',
        'wareStatusValue',
        'itemNum',
        'barCode',
        'colType',
        'startCreatedTime',
        'endCreatedTime',
        'startJdPrice',
        'endJdPrice',
        'startOnlineTime',
        'endOnlineTime',
        'startModifiedTime',
        'endModifiedTime',
        'startOfflineTime',
        'endOfflineTime',
        'startStockNum',
        'endStockNum',
        'orderField',
        'orderType',
        'pageNo',
        'pageSize',
        'transportId',
        'claim',
        'groupId',
        'multiCategoryId',
        'warePropKey',
        'warePropValue',
        'field',
    ];

    protected $commaSeparatedParams = ['wareId', 'field'];

    protected $defaultParamValues = [];

    public function check()
    {
        // RequestCheckUtil::checkNotNull($this->fields, "fields");
    }

}