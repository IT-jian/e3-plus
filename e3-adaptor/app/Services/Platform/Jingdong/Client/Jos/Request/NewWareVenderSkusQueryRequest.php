<?php


namespace App\Services\Platform\Jingdong\Client\Jos\Request;


use App\Services\Platform\Jingdong\Client\Jos\JosRequest;


/**
 * 根据venderId查询sku列表（自营/pop通用）
 *
 * Class NewWareVenderSkusQueryRequest
 *
 * @package App\Services\Platform\Jingdong\Client\Jos\Request
 *
 * @method $this setIndex($index) 分页查询起点
 *
 * jingdong_new_ware_vender_skus_query_responce.search_result.skuList
 */
class NewWareVenderSkusQueryRequest extends JosRequest
{
    /**
     * 接口名称
     *
     * @var string
     * @see https://open.jd.com/home/home#/doc/api?apiCateId=48&apiId=2289&apiName=jingdong.new.ware.vender.skus.query
     */
    protected $apiName = 'jingdong.new.ware.vender.skus.query';

    protected $paramKeys = [
        'index',
    ];

    protected $defaultParamValues = [
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->index, "index");
    }
}