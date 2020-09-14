<?php


namespace App\Services\Platform\Jingdong\Client\Jos\Request;


use App\Services\Platform\Jingdong\Client\Jos\JosRequest;


/**
 * 评价
 *
 * Class AscCommonCancelRequest
 *
 * @package App\Services\Platform\Jingdong\Client\Jos\Request
 *
 * @method $this setSkuids($value) sku列表 
 * @method $this setWareName($value) 商品名 
 * @method $this setBeginTime($value) 开始时间 
 * @method $this setEndTime($value) 结束时间 
 * @method $this setScore($value) 评价等级（全部0/好3/中2/差评1） 
 * @method $this setContent($value) 评价关键字（评价内容） 
 * @method $this setPin($value) 用户账号 
 * @method $this setIsVenderReply($value) 商家是否回复 
 * @method $this setCid($value) 类目ID 
 * @method $this setOrderIds($value) 订单号(最多50个 用,分隔) 
 * @method $this setPage($value) 翻页 
 * @method $this setPageSize($value) 每页条数(最大50) 

 *
 * jingdong_pop_PopCommentJsfService_getVenderCommentsForJos_responce.comments
 */
class GetVenderCommentsForJosRequest extends JosRequest
{
    /**
     * 接口名称
     *
     * @var string
     * @see https://open.jd.com/home/home#/doc/api?apiCateId=102&apiId=2442&apiName=jingdong.pop.PopCommentJsfService.getVenderCommentsForJos
     */
    protected $apiName = 'jingdong.pop.PopCommentJsfService.getVenderCommentsForJos';

    protected $paramKeys = [
        'beginTime',
        'endTime',
        'score',
        'skuids',
        'wareName',
        'page',
        'pageSize',
        'content',
        'pin',
        'isVenderReply',
        'cid',
        'orderIds',
        'open_id_buyer',
    ];

    public function check()
    {

    }
}
