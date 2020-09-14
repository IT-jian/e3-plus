<?php


namespace App\Services\Hub\Hufu\Adidas\Request;


use App\Models\SysStdExchange;
use App\Models\SysStdTrade;
use App\Services\Hub\Adidas\JingdongRequest\Transformer\ExchangeCreateTransformer;
use Illuminate\Contracts\Support\Arrayable;
use App\Services\Hub\Adidas\JingdongRequest\ExchangeCreateRequest as BaseRequest;

/**
 * 换货单加强版报文组织 -- 走虎符
 *
 * Class ExchangeCreateRequest
 * @package App\Services\Hub\Adidas\Request
 */
class ExchangeCreateRequest extends BaseRequest
{
    use RequestTrait;

    public function setContent($id)
    {
        if (is_array($id) || $id instanceof Arrayable) {
            $stdExchange = $id;
        } else {
            $stdExchange = SysStdExchange::where('dispute_id', $id)->first();
        }
        $stdTrade = SysStdTrade::where('tid', $stdExchange['tid'])->first();
        // 设置时间戳
        $this->setDataVersion(strtotime($stdExchange['modified']));
        $this->keyword = $stdExchange['dispute_id'] ?? '';

        $data = $this->getExtendProps($stdTrade, $stdExchange);

        $this->setExtendQuery($data, $stdTrade['shop_code']);

        return $this;
    }

    public function getExtendProps($stdTrade, $stdExchange)
    {
        $exchangeCreateXml = $this->getTransformer()->format($stdExchange);
        $extendProps = [
            'type' => 'exchangeCreate',
            'buyer_nick' => $stdTrade['buyer_nick'],
            'buyer_email' => $stdTrade['buyer_email'],
            'receiver_address' => $stdTrade['receiver_address'],
            'receiver_mobile' => $stdTrade['receiver_mobile'],
            'receiver_name' => $stdTrade['receiver_name'],
            'exchange_buyer_name' => $stdTrade['buyer_name'],
            'exchange_buyer_address' => $stdTrade['buyer_address'],
            'exchange_seller_address' => $stdTrade['seller_address'],
            'exchange_buyer_phone' => $stdTrade['buyer_phone'],
            'content' => $exchangeCreateXml
        ];

        return $extendProps;
    }

    /**
     * xml 组装的报文
     *
     * @return ExchangeCreateTransformer
     */
    public function getTransformer()
    {
        return app()->make(ExchangeCreateTransformer::class);
    }
}
