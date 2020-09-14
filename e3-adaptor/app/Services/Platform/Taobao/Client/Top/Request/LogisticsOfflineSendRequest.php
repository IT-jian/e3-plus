<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * Class LogisticsOfflineSendRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setSubTid($value)
 * @method $this setTid($value)
 * @method $this setIsSplit($value)
 * @method $this setOutSid($value)
 * @method $this setCompanyCode($value)
 * @method $this setSenderId($value)
 * @method $this setCancelId($value)
 * @method $this setFeature($value)
 * @method $this setSellerIp($value)
 *
 * @author linqihai
 * @since 2019/12/31 16:32
 */
class LogisticsOfflineSendRequest extends TopRequest
{
    /**
     * 接口名称
     *
     * @var string
     * @see https://open.taobao.com/api.htm?spm=a219a.7386797.0.0.4afc669aOKdRAr&source=search&docId=10690&docType=2
     */
    protected $apiName = 'taobao.logistics.offline.send';

    protected $commaSeparatedParams = [
        'subTid',
    ];

    protected $paramKeys = [
        'subTid',
        'tid',
        'isSplit',
        'outSid',
        'companyCode',
        'senderId',
        'cancelId',
        'feature',
        'sellerIp',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
    }
}
