<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 库存更新
 *
 * Class SkusQuantityUpdateRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setNumIid($value)
 * 库存更新方式，可选。1为全量更新，2为增量更新。
 * 如果不填，默认为全量更新。当选择全量更新时，如果库存更新值传入的是负数，会出错并返回错误码；
 * 当选择增量更新时，如果库存更新值为负数且绝对值大于当前库存，则sku库存会设置为0.
 * @method $this setType($value)
 * sku库存批量修改入参，用于指定一批sku和每个sku的库存修改值，特殊可填。格式为skuId:库存修改值;skuId:库存修改值。最多支持20个SKU同时修改。
 * @method $this setSkuidQuantities($value)
 * 特殊可选，skuIdQuantities为空的时候用该字段通过outerId来指定sku和其库存修改值。
 * 格式为outerId:库存修改值;outerId:库存修改值。
 * 当skuIdQuantities不为空的时候该字段失效。
 * 当一个outerId对应多个sku时，所有匹配到的sku都会被修改库存。最多支持20个SKU同时修改。
 * @method $this setOuteridQuantities($value)
 *
 * @author linqihai
 * @since 2020/4/29 10:29
 * @see https://open.taobao.com/api.htm?spm=a219a.7386653.0.0.5f85669aRoNcw3&source=search&docId=21169&docType=2
 */
class SkusQuantityUpdateRequest extends TopRequest
{
    protected $apiName = 'taobao.skus.quantity.update';

    /**
     * 入参字段
     * @var array
     */
    protected $paramKeys = [
        'numIid',
        'type',
        'skuidQuantities',
        'outeridQuantities',
    ];

    public function check()
    {
        // RequestCheckUtil::checkNotNull($this->numIid, "num_iid");
    }
}