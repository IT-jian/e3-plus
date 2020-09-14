<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 开票申请数据获取接口
 *
 * Class EinvoiceApplyGetRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setPlatformTid($tid))
 * @method $this setApplyId($applyId)
 */
class EinvoiceApplyGetRequest extends TopRequest
{
    public $requireHttps = true;

    protected $apiName = 'alibaba.einvoice.apply.get';

    protected $commaSeparatedParams = [
        'fields',
    ];

    protected $paramKeys    = [
        'platformTid',
        'applyId',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->platformTid, "platform_tid");
    }
}
