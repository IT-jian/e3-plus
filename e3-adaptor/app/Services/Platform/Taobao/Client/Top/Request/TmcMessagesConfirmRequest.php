<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 确认消费消息的状态
 *
 * Class TmcMessagesConfirmRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setGroupName($value) 分组名称，不传代表默认分组
 * @method $this setsMessageIds($value) 处理成功的消息ID列表 最大 200个ID
 * @method $this setfMessageIds($value)
 *
 * @see https://open.taobao.com/api.htm?docId=21985&docType=2&source=search
 *
 * tmc_messages_confirm_response.is_success
 */
class TmcMessagesConfirmRequest extends TopRequest
{
    protected $apiName = 'taobao.tmc.messages.confirm';
    /**
     * 逗号分隔的字段
     *
     * @var array
     */
    protected $commaSeparatedParams = [
        'sMessageIds',
        'fMessageIds',
    ];

    protected $paramKeys = [
        'groupName',
        'sMessageIds',
        'fMessageIds',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->sMessageIds, "sMessageIds");
    }
}
