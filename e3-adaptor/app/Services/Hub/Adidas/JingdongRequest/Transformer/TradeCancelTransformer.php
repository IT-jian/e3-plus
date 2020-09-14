<?php


namespace App\Services\Hub\Adidas\JingdongRequest\Transformer;


use App\Models\JingdongRefundApply;
use App\Models\Sys\Shop;
use App\Models\SysStdTradeItem;
use App\Services\Adaptor\Jingdong\Api\RefundApplyQuery;
use App\Services\Hub\Adidas\Request\Transformer\BaseTransformer;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * 订单状态变更，触发订单取消，组织订单取消 格式
 *
 * Class TradeCancelTransformer
 * @package App\Services\Hub\Adidas\JingdongRequest\Transformer
 *
 */
class TradeCancelTransformer extends BaseTransformer
{
    public function format($stdTrade)
    {

        $where = [
            'tid' => $stdTrade['tid'],
            'platform'  => $stdTrade['platform'],
        ];
        $tradeItems = SysStdTradeItem::where($where)->get();

        $refundApply = $this->getRefundApply($stdTrade);
        $rootElement = $this->getRootElement($stdTrade, $refundApply);
        $content = $this->getContent($stdTrade, $tradeItems, $refundApply);

        return ArrayToXml::convert($content, $rootElement, false, 'UTF-8');
    }

    protected function getRootElement($trade, $refundApply)
    {
        // 查询京东整单退款原因
        $reason = $this->getReason($refundApply);
        return [
            'rootElementName' => 'Order',
            '_attributes' => [
                'Action'                 => 'Cancel',
                'DocumentType'           => '0001',
                'EnterpriseCode'         => $trade['shop_code'],
                'OrderNo'                => $this->generatorOrderNo($trade['tid'], $trade['platform']),
                'CustomerPONo'           => (string)$trade['tid'],
                'ModificationReasonCode' => $reason ?? 'B03',
            ],
        ];
    }

    /**
     * 查询退款申请
     *
     * @param $trade
     * @return JingdongRefundApply
     */
    public function getRefundApply($trade)
    {
        $refundApply = JingdongRefundApply::where('order_id', $trade['tid'])->orderBy('id', 'desc')->whereIn('status', [0, 3])->first();
        if (empty($refundApply)) {
            $shop = Shop::where('code', $trade['shop_code'])->firstOrFail();
            $refundApplyApi = new RefundApplyQuery($shop);
            $refunds = $refundApplyApi->find(['order_id' => $trade['tid']]);
            if (!empty($refunds)) {
                foreach ($refunds as $refund) {
                    if (in_array($refund['status'], [0, 3])) {
                        $refundApply = $refund;
                    }
                }
            }
        }

        return $refundApply;
    }

    public function getReason($refundApply)
    {
        if ($refundApply['reason']) {
            $reason = $this->tradeCancelReasonCodeMap($refundApply['reason'], 'jingdong');
        }

        return $reason ?? 'B03';
    }

    protected function getContent($trade, $tradeItems, $refundApply)
    {
        $orderLines = [];
        if (!empty($tradeItems)) {
            $orderLines = $this->formatOrderLines($tradeItems->toArray(), $refundApply);
        }

        $content = [
            'OrderLines' => [
                'OrderLine' => $orderLines
            ],
        ];

        return $content;
    }

    protected function formatOrderLines($tradeItems, $refundApply)
    {
        $orderLines = [];
        if (empty($tradeItems)) {
            return $orderLines;
        }
        foreach ($tradeItems as $item) {
            $orderLine = [
                '_attributes' => [
                    'PrimeLineNo'      => $item['row_index'],
                    'Action'           => 'CANCEL',
                    'QuantityToCancel' => $item['num'],
                    'CustomerLinePONo' => $item['row_index'],
                ],
                'Extn'        => [
                    '_attributes' => [
                        'ExtnRefundId'     => $refundApply['id'],
                        'ExtnRefundAmount' => $item['payment'],
                    ],
                ],
            ];
            $orderLines[] = $orderLine;
        }

        return $orderLines;
    }
}
