<?php


namespace App\Services\Platform\Jingdong\Client\Jos\Request;


use App\Services\Platform\Jingdong\Client\Jos\JosRequest;


/**
 * 京东拆单申请
 * Class CommitOrderSplitApplyApiRequest
 *
 * @package App\Services\Platform\Jingdong\Client\Jos\Request
 *
 * @method $this setParam($value) 请求参数
 *
 * jingdong_commitOrderSplitApplyApi_response.apiSafResult.success
 */
class CommitOrderSplitApplyApiRequest extends JosRequest
{
    /**
     * 接口名称
     *
     * @var string
     * @see https://jos.jd.com/api/detail.htm?apiName=jingdong.commitOrderSplitApplyApi&id=828
     */
    protected $apiName = 'jingdong.commitOrderSplitApplyApi';

    protected $paramKeys = [
        'param',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->param, "param");
    }
}