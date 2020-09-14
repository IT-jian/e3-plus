<?php


namespace App\Services\Wms\Baozun\Adidas\Request;


use App\Models\SysStdRefund;
use App\Services\Wms\Contracts\RequestContract;
use App\Services\Wms\Baozun\Adidas\BaseRequest;
use Illuminate\Contracts\Support\Arrayable;

/**
 * 订单取消信息推送
 * 订单发货之前申请退款，并且退款成功之后下发
 *
 * Class TradeCancelSuccessRequest
 * @package App\Services\Wms\Shunfeng\Adidas\Request
 */
class TradeCancelSuccessRequest extends BaseRequest  implements RequestContract
{
    protected $apiName = 'OutboundShipmentCancel';

    public $format = 'json';

    public $keyword = '';

    public function setContent($id)
    {
        if (is_array($id) || $id instanceof Arrayable) {
            $stdRefund = $id;
        } else {
            $stdRefund = SysStdRefund::where('refund_id', $id)->first();
        }
        $orderLineNos = [];
        if ('taobao' == $stdRefund['platform']) {
            foreach ($stdRefund->items as $item) {
                $orderLineNos[] = [
                    'lineNo' => $item['oid'],
                ];
            }

            if (empty($orderLineNos)) {
                throw new \Exception('refund item not found');
            }
        }
        $this->keyword = $stdRefund['refund_id'] ?? '';
        $this->data = [
            'orderCode' => $stdRefund['tid'],
            'orderLineNos' => $orderLineNos,
        ];

        return $this;
    }
}
