<?php


namespace App\Services\Platform\Jingdong\Client\Jos\Request;


use App\Services\Platform\Jingdong\Client\Jos\JosRequest;

/**
 * Class SkuReadSearchSkuListRequest
 * @package App\Services\Platform\Jingdong\Client\Jos\Request
 *
 * @method $this setWareId($value) 商品ID 
 * @method $this setSkuId($value) SKU ID 
 * @method $this setSkuStatuValue($value) SKU状态：1:上架 2:下架 4:删除 默认查询上架下架商品 
 * @method $this setMaxStockNum($value) 库存范围 最大库存 
 * @method $this setMinStockNum($value) 库存范围 最小库存 
 * @method $this setEndCreatedTime($value) 创建时间结束 
 * @method $this setEndModifiedTime($value) 修改时间结束 
 * @method $this setStartCreatedTime($value) 创建时间开始 
 * @method $this setStartModifiedTime($value) 修改时间开始 
 * @method $this setOutId($value) 外部ID 
 * @method $this setColType($value) 合作类型 
 * @method $this setItemNum($value) 货号 
 * @method $this setWareTitle($value) 商品名称 
 * @method $this setOrderFiled($value) 排序字段.目前支持skuId、stockNum 
 * @method $this setOrderType($value) 排序类型：asc、desc 
 * @method $this setPageNo($value) 页码 从1开始 
 * @method $this setField($value) 自定义返回字段 
 * jingdong_sku_read_searchSkuList_response.page.data
 */
class SkuReadSearchSkuListRequest extends JosRequest
{
    /**
     * @var string
     * @see https://open.jd.com/home/home#/doc/api?apiCateId=48&apiId=1587&apiName=jingdong.ware.read.searchWare4Valid
     */
    protected $apiName = 'jingdong.sku.read.searchSkuList';

    /**
     * 入参字段
     * @var array
     */
    protected $paramKeys = [
        'wareId',
        'skuId',
        'skuStatuValue',
        'maxStockNum',
        'minStockNum',
        'endCreatedTime',
        'endModifiedTime',
        'startCreatedTime',
        'startModifiedTime',
        'outId',
        'colType',
        'itemNum',
        'wareTitle',
        'orderFiled',
        'orderType',
        'pageNo',
        'page_size',
        'field',
    ];


    protected $defaultParamValues = [];

    protected $commaSeparatedParams = ['wareId', 'field', 'skuId', 'skuStatuValue'];

    public function setPageSize($value)
    {
        $this->data['page_size'] = $value;
    }

    public function check()
    {
        // RequestCheckUtil::checkNotNull($this->fields, "fields");
    }

}
