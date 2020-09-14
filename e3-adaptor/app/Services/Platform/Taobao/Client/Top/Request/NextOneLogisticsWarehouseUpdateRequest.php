<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;

/**
 * 商家上传退货入仓状态给ag
 *
 * Class NextOneLogisticsWarehouseUpdateRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setRefundId($value)
 * @method $this setWarehouseStatus($value)
 *
 * @author linqihai
 * @since 2020/1/2 10:21
 * @see https://open.taobao.com/api.htm?docId=28206&docType=2&source=search
 */
class NextOneLogisticsWarehouseUpdateRequest extends TopRequest
{
    protected $apiName = 'taobao.nextone.logistics.warehouse.update';

    /**
     * 入参字段
     * @var array
     */
    protected $paramKeys    = [
        'refundId',
        'warehouseStatus',
    ];

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->fields, "fields");
    }
}