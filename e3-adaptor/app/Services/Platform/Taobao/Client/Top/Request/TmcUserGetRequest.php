<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * ( 获取用户已开通消息 )
 * topic覆盖更新,务必传入全量topic，或者不传topics，使用appkey订阅的所有topic
 *
 * Class TmcUserGetRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setNick($value) 用户昵称
 * @method $this setFields($value) 需返回的字段列表，多个字段以半角逗号分隔。可选值：TmcUser结构体中的所有字段，一定要返回topic。
 *
 * @see https://open.taobao.com/api.htm?docId=21990&docType=2&source=search
 *
 * tmc_user_get_response.tmc_user
 */
class TmcUserGetRequest extends TopRequest
{
    protected $apiName = 'taobao.tmc.user.get';
    /**
     * 逗号分隔的字段
     *
     * @var array
     */
    protected $commaSeparatedParams = [
        'fields',
    ];

    protected $defaultParamValues = [
        'fields' => 'user_nick,topics,user_id,is_valid,created,modified',
    ];
    protected $paramKeys = [
        'fields',
        'nick',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
        //RequestCheckUtil::checkNotNull($this->nick, "nick");
    }
}
