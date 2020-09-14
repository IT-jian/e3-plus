<?php


namespace App\Services\Hub\Adidas\JingdongRequest\Transformer;


use App\Services\Hub\Adidas\Request\Transformer\RefundReturnCreateExtendTransformer as BaseTransformer;

class RefundReturnCreateExtendTransformer extends BaseTransformer
{
    protected function getContent($refund, $trade)
    {
        $content = parent::getContent($refund, $trade);
        $tradeCreateTransformer = new TradeCreateTransformer();
        $fapiaoLines = $tradeCreateTransformer->formatFapiaoLines($trade);
        if ($fapiaoLines) {
            $content['Order']['Extn']['ADSHeaderDetailsList'] = $fapiaoLines;
            $content['Order']['Extn']['_attributes']['ExtnFapiaoRequest'] = 'Y';
        } else {
            $content['Order']['Extn']['_attributes']['ExtnFapiaoRequest'] = 'N';
        }

        return $content;
    }

    public function lineCharges($params, $quantity = 1)
    {
        $tradeCreateTransformer = new TradeCreateTransformer();
        $itemPromotions = $tradeCreateTransformer->getItemsPromotions($params['trade']);
        $skuId = $params['item']['sku_id'];
        $promotions = $itemPromotions[$skuId] ?? [];
        $lineCharges = [];
        if (!empty($promotions)) {
            $lineCharges = (new TradeCreateTransformer())->formatOrderLinePromotions($params['item'], $promotions);
        }
        if (empty($lineCharges)) {
            return [];
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

    /**
     * 明细退款金额 -- 取京东的订单明细实付金额
     *
     * @param $refund
     * @param $refundItem
     * @param $tradeItem
     * @return mixed
     */
    public function getExtnRefundAmount($refund, $refundItem, $tradeItem)
    {
        return $tradeItem['payment'];
    }

    // 京东退货物流公司
    public function getShippingName($refund)
    {
        return '';
    }
}
