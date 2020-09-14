<?php


namespace App\Services\Hub\Adidas\Request\Transformer;


use App\Models\SysStdRefundItem;
use App\Models\SysStdTradeItem;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * 退单下载触发订单取消，组织订单取消 格式
 *
 * Class TradeCancelTransformer
 * @package App\Services\Hub\Adidas\Request\Transformer
 *
 * @author linqihai
 * @since 2020/1/6 15:10
 */
class TradeCancelTransformer extends BaseTransformer
{
    public function format($stdRefund)
    {

        $where = [
            'refund_id' => $stdRefund['refund_id'],
            'platform'  => $stdRefund['platform'],
        ];
        $refundItems = SysStdRefundItem::where($where)->get();

        $rootElement = $this->getRootElement($stdRefund, $refundItems);
        $content = $this->getContent($stdRefund, $refundItems);

        return ArrayToXml::convert($content, $rootElement, false, 'UTF-8');
    }

    protected function getRootElement($refund, $refundItems)
    {
        $item = $refundItems[0];
        return [
            'rootElementName' => 'Order',
            '_attributes' => [
                'Action'                 => 'MODIFY',
                'DocumentType'           => '0001',
                'EnterpriseCode'         => $refund['shop_code'],
                'OrderNo'                => $this->generatorOrderNo($refund['tid'], $refund['platform']),
                'CustomerPONo'           => (string)$refund['tid'],
                'ModificationReasonCode' => $this->tradeCancelReasonCodeMap($item['reason'], $refund['platform']),
            ],
        ];
    }

    protected function getContent($refund, $refundItems)
    {
        $orderLines = [];
        if (!empty($refundItems)) {
            $orderLines = $this->formatOrderLines($refund, $refundItems->toArray());
        }

        $content = [
            'OrderLines' => [
                'OrderLine' => $orderLines
            ],
        ];

        return $content;
    }

    protected function formatOrderLines($refund, $refundItems)
    {
        $orderLines = [];
        if (empty($refundItems)) {
            return $orderLines;
        }
        foreach ($refundItems as $item) {
            $where = ['tid' => $refund['tid'], 'oid' => $item['oid']];
            $tradeItem = SysStdTradeItem::where($where)->first(['row_index']);
            $rowIndex = $tradeItem['row_index'] ?? $item['row_index'];
            $orderLine = [
                '_attributes' => [
                    'PrimeLineNo'      => $rowIndex,
                    'Action'           => 'CANCEL',
                    'QuantityToCancel' => $item['num'],
                    'CustomerLinePONo' => empty($refund['oid']) ? $rowIndex : $refund['oid'],
                ],
                'Extn'        => [
                    '_attributes' => [
                        'ExtnRefundId'     => $refund['refund_id'],
                        'ExtnRefundAmount' => $refund['refund_fee'],
                    ],
                ],
            ];
            $orderLines[] = $orderLine;
        }

        return $orderLines;
    }
}
