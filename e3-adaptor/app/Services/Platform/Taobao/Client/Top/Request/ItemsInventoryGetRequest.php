<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 当前用户作为卖家的仓库中的商品列表
 * 获取当前用户作为卖家的仓库中的商品列表，并能根据传入的搜索条件对仓库中的商品列表进行过滤 只能获得商品的部分信息，商品的详细信息请通过taobao.item.seller.get获取
 * Class ItemsInventoryGetRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setFields($value)
 *
 * @see https://open.taobao.com/api.htm?docId=162&docType=2
 */
class ItemsInventoryGetRequest extends TopRequest
{
    protected $apiName = 'taobao.skus.quantity.update';

    /**
     * 入参字段
     * @var array
     */
    protected $paramKeys = [
        'fields',
        'q',
        'banner',
        'cid',
        'seller_cids',
        'page_no',
        'page_size',
        'has_discount',
        'order_by',
        'is_taobao',
        'is_ex',
        'start_modified',
    ];


    protected $defaultParamValues = [
        'fields' => 'approve_status,num_iid,title,nick,type,cid,pic_url,num,props,valid_thru, list_time,price,
        has_discount,has_invoice,has_warranty,has_showcase, modified,delist_time,postage_id,seller_cids,outer_id',
    ];


    public function check()
    {
        // RequestCheckUtil::checkNotNull($this->fields, "fields");
    }
}