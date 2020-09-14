<?php


namespace App\Services\Platform\Jingdong\Client\Jos\Request;


use App\Services\Platform\Jingdong\Client\Jos\JosRequest;


/**
 * 单个sku查询
 *
 * Class SkuReadFindSkuByIdRequest
 *
 * @package App\Services\Platform\Jingdong\Client\Jos\Request
 *
 * @method $this setSkuId($value) 商品编号
 * @method $this setField($value) 字段
 *
 * jingdong_sku_read_findSkuById_responce.sku
 */
class SkuReadFindSkuByIdRequest extends JosRequest
{
    /**
     * 接口名称
     *
     * @var string
     * @see https://jos.jd.com/api/detail.htm?apiName=jingdong.asc.receive.list&id=2114
     */
    protected $apiName = 'jingdong.sku.read.findSkuById';

    protected $paramKeys = [
        'skuId', // 商品编号
        'field', // 字段
    ];

    protected $commaSeparatedParams = ['field'];

    protected $defaultParamValues = [
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->skuId, "skuId");
    }
}