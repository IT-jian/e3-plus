<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 为已授权的用户开通消息服务
 * topic覆盖更新,务必传入全量topic，或者不传topics，使用appkey订阅的所有topic
 *
 * Class TmcUserPermitRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setTopics($value) 消息主题列表，用半角逗号分隔。当用户订阅的topic是应用订阅的子集时才需要设置，不设置表示继承应用所订阅的所有topic，一般情况建议不要设置。
 *
 * @see https://open.taobao.com/api.htm?docId=21990&docType=2&source=search
 *
 * tmc_user_permit_response.is_success
 */
class TmcUserPermitRequest extends TopRequest
{
    protected $apiName = 'taobao.tmc.user.permit';
    /**
     * 逗号分隔的字段
     *
     * @var array
     */
    protected $commaSeparatedParams = [
        'topics',
    ];

    protected $paramKeys = [
        'topics',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->sMessageIds, "sMessageIds");
    }
}
