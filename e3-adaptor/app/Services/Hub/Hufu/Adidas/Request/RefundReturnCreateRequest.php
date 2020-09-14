<?php


namespace App\Services\Hub\Hufu\Adidas\Request;


use App\Models\SysStdRefund;
use App\Models\SysStdTrade;
use App\Services\Hub\Adidas\JingdongRequest\Transformer\RefundReturnCreateTransformer;
use Illuminate\Contracts\Support\Arrayable;
use App\Services\Hub\Adidas\JingdongRequest\RefundReturnCreateRequest as BaseRequest;

/**
 * 退货加强版报文组织 -- 走奇门
 *
 * Class RefundReturnCreateRequest
 * @package App\Services\Hub\Adidas\Request
 */
class RefundReturnCreateRequest extends BaseRequest
{
    use RequestTrait;

    public function setContent($id)
    {
        if (is_array($id) || $id instanceof Arrayable) {
            $stdRefund = $id;
        } else {
            $stdRefund = SysStdRefund::where('refund_id', $id)->first();
        }
        $stdTrade = SysStdTrade::where('tid', $stdRefund['tid'])->first();
        // 设置时间戳
        $this->setDataVersion(strtotime($stdTrade['modified']));
        $this->keyword = $stdRefund['refund_id'] ?? '';

        $data = $this->getExtendProps($stdTrade, $stdRefund);

        $this->setExtendQuery($data, $stdTrade['shop_code']);

        return $this;
    }

    public function getExtendProps($stdTrade, $stdRefund)
    {
        $refundCreateXml = $this->getTransformer()->format($stdRefund);

        $extendProps = [
            'type' => 'refundReturnCreate',
            'buyer_nick' => $stdTrade['buyer_nick'],
            'buyer_email' => $stdTrade['buyer_email'],
            'receiver_address' => $stdTrade['receiver_address'],
            'receiver_mobile' => $stdTrade['receiver_mobile'],
            'receiver_name' => $stdTrade['receiver_name'],
            'content' => $refundCreateXml
        ];

        return $extendProps;
    }

    /**
     * xml 组装的报文
     *
     * @return RefundReturnCreateTransformer
     */
    public function getTransformer()
    {
        return app()->make(RefundReturnCreateTransformer::class);
    }
}
