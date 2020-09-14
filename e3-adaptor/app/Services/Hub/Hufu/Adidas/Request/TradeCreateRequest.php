<?php


namespace App\Services\Hub\Hufu\Adidas\Request;


use App\Models\JingdongTrade;
use App\Models\SysStdTrade;
use App\Services\Hub\Adidas\JingdongRequest\Transformer\TradeCreateTransformer;
use Illuminate\Contracts\Support\Arrayable;
use App\Services\Hub\Adidas\JingdongRequest\TradeCreateRequest as BaseRequest;

/**
 * 订单创建
 *
 * Class TradeCreateRequest
 * @package App\Services\Hub\Adidas\Request
 */
class TradeCreateRequest extends BaseRequest
{
    use RequestTrait;

    public function setContent($id)
    {
        if (is_array($id) || $id instanceof Arrayable) {
            $stdTrade = $id;
        } else {
            $stdTrade = SysStdTrade::where('tid', $id)->first();
        }
        // 设置时间戳
        $this->setDataVersion(strtotime($stdTrade['modified']));
        $this->keyword = $stdTrade['tid'] ?? '';

        $data = $this->getExtendProps($stdTrade);

        $this->setExtendQuery($data, $stdTrade['shop_code']);

        return $this;
    }

    public function getExtendProps($stdTrade)
    {
        $tradeCreateXml = $this->getTransformer()->format($stdTrade);
        $jingdongTrade = JingdongTrade::where('order_id', $stdTrade['tid'])->first();
        $invoiceEasyInfo = data_get($jingdongTrade->origin_content, 'invoiceEasyInfo', []);
        $vatInfo = data_get($jingdongTrade->origin_content, 'vatInfo', []);
        $extendProps = [
            'type' => 'tradeCreate',
            'buyer_nick' => $stdTrade['buyer_nick'],
            'buyer_email' => $stdTrade['buyer_email'],
            'invoice_title' => $invoiceEasyInfo['invoiceTitle'] ?? '',
            'bank_account' => $vatInfo['bankAccount'] ?? '',
            // 增值税发票
            'phoneRegIstered' => $vatInfo['phoneRegIstered'] ?? '',
            'userAddress' => $vatInfo['userAddress'] ?? '',
            'userPhone' => $vatInfo['userPhone'] ?? '',
            'receiver_mobile' => $stdTrade['receiver_mobile'],
            'receiver_address' => $stdTrade['receiver_address'],
            'receiver_name' => $stdTrade['receiver_name'],
            'content' => $tradeCreateXml,
            'holds' => [
                'seller_memo' => $stdTrade['seller_memo'],
                'buyer_message' => $stdTrade['buyer_message'],
            ],
        ];

        return $extendProps;
    }

    /**
     * xml 组装的报文
     *
     * @return TradeCreateTransformer
     */
    public function getTransformer()
    {
        return app()->make(TradeCreateTransformer::class);
    }
}
