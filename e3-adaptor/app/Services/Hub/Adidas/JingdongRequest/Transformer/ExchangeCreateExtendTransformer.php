<?php


namespace App\Services\Hub\Adidas\JingdongRequest\Transformer;


use App\Services\Hub\Adidas\Request\Transformer\ExchangeCreateExtendTransformer as BaseTransformer;

/**
 * 换货单新增 加强版报文
 *
 * Class ExchangeCreateExtendTransformer
 * @package App\Services\Hub\Adidas\JingdongRequest\Transformer
 *
 */
class ExchangeCreateExtendTransformer extends BaseTransformer
{
    protected function formatReturnOrder($exchange, $items, $trade)
    {
        $content = parent::formatReturnOrder($exchange, $items, $trade);
        $tradeCreateTransformer = new TradeCreateTransformer();
        $fapiaoLines = $tradeCreateTransformer->formatFapiaoLines($trade);
        if ($fapiaoLines) {
            $content['Extn']['ADSHeaderDetailsList'] = $fapiaoLines;
            $content['Extn']['_attributes']['ExtnFapiaoRequest'] = 'Y';
        } else {
            $content['Extn']['_attributes']['ExtnFapiaoRequest'] = 'N';
        }

        return $content;
    }

    public function lineCharges($params, $quantity = 1)
    {
        $tradeCreateTransformer = new TradeCreateTransformer();
        $itemPromotions = $tradeCreateTransformer->getItemsPromotions($params['trade']);
        $promotions = $itemPromotions[$params['item']['sku_id']] ?? [];
        $lineCharges = [];
        if (!empty($promotions)) {
            $lineCharges = (new TradeCreateTransformer())->formatOrderLinePromotions($params['item'], $promotions);
        }

        $tradeItemQuantity = $params['item']['num'] ?? 1;
        if ($tradeItemQuantity == $quantity) { // 数量相等不计算
            return $lineCharges;
        }
        foreach ($lineCharges as $key => $lineCharge) { // 计算均摊差额
            $lineCharges[$key]['_attributes']['ChargePerLine'] = round(($lineCharge['_attributes']['ChargePerLine'] / $tradeItemQuantity) * $quantity, 2);
        }

        return $lineCharges;
    }

    public function exchangeShippingInfo($exchange, $trade)
    {
        // 京东换货单地址，取原单地址
        return $this->shippingInfo($trade);
    }
}
