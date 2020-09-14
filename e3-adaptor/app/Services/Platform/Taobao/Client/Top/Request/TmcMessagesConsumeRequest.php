<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 消费多条消息
 *
 * Class TmcMessagesConsumeRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setGroupName($value) 用户分组名称，不传表示消费默认分组，如果应用没有设置用户分组，传入分组名称将会返回错误
 * @method $this setQuantity($quantity) 每次批量消费消息的条数，最小值：10；最大值：200
 * @see https://open.taobao.com/api.htm?docId=21986&docType=2&source=search
 *
 * tmc_messages_consume_response.messages.tmc_message
 */
class TmcMessagesConsumeRequest extends TopRequest
{
    protected $apiName = 'taobao.tmc.messages.consume';
    /**
     * 逗号分隔的字段
     *
     * @var array
     */
    protected $commaSeparatedParams = [
    ];

    protected $paramKeys = [
        'groupName',
        'quantity',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->sMessageIds, "sMessageIds");
    }
}
