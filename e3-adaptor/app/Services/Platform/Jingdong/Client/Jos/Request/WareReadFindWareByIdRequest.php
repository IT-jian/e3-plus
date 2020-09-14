<?php


namespace App\Services\Platform\Jingdong\Client\Jos\Request;


use App\Services\Platform\Jingdong\Client\Jos\JosRequest;


/**
 * 单个商品查询
 *
 * Class WareReadFindWareByIdRequest
 *
 * @package App\Services\Platform\Jingdong\Client\Jos\Request
 *
 * @method $this setWareId($value) 商品编号
 * @method $this setField($value) 字段
 *
 * jingdong_ware_read_findWareById_responce.ware
 */
class WareReadFindWareByIdRequest extends JosRequest
{
    /**
     * 接口名称
     *
     * @var string
     * @see https://jos.jd.com/api/detail.htm?apiName=jingdong.asc.receive.list&id=2114
     */
    protected $apiName = 'jingdong.ware.read.findWareById';

    protected $paramKeys = [
        'wareId', // 商品编号
        'field', // 字段
    ];

    protected $commaSeparatedParams = ['field'];

    protected $defaultParamValues = [
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->wareId, "wareId");
    }
}