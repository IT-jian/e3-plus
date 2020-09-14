<?php


namespace App\Services\Hub\Adidas\Request\Transformer;


use App\Models\SysStdRefundItem;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * 退货退款取消
 *
 * Class RefundReturnCancelTransformer
 * @package App\Services\Hub\Adidas\Request\Transformer
 *
 * @author linqihai
 * @since 2020/1/6 15:10
 */
class RefundReturnCancelTransformer extends BaseTransformer
{
    public function format($stdRefund)
    {
        $rootElement = $this->getRootElement($stdRefund);
        $content = $this->getContent($stdRefund);

        return ArrayToXml::convert($content, $rootElement, false, 'UTF-8');
    }

    private function getRootElement($refund)
    {
        return [
            'rootElementName' => 'Order',
            '_attributes'     => [
                'Action'                 => 'CANCEL',
                'Override'               => 'Y',
                'CustomerPONo'           => (string)$refund['refund_id'],
                'OrderNo'                => $this->generatorRefundNo($refund['refund_id'], $refund['platform']),
                'EnterpriseCode'         => $refund['shop_code'],
                'SellerOrganizationCode' => $refund['shop_code'],
                'DocumentType'           => '0003',
                'OrderPurpose'           => 'RETURN',
                'ModificationReasonCode' => 'A11',
            ],
        ];
    }

    private function getContent($refund)
    {
        $where = [
            'refund_id' => $refund['refund_id'],
            'platform'  => $refund['platform'],
        ];
        $orderLines = [];
        // 查询明细
        $refundItems = SysStdRefundItem::where($where)->get();
        if (!empty($refundItems)) {
            $orderLines = $this->formatOrderLines($refund, $refundItems->toArray());
        }
        $content = [
            'OrderLines' => [
                'OrderLine' => $orderLines,
            ],
        ];

        return $content;
    }

    /**
     * 退单取消明细
     *
     * @param $refund
     * @param $refundItems
     * @return array
     *
     * @author linqihai
     * @since 2020/2/17 17:19
     */
    private function formatOrderLines($refund, $refundItems)
    {
        $orderLines = [];
        if (empty($refundItems)) {
            return $orderLines;
        }
        foreach ($refundItems as $item) {
            $orderLine = [
                '_attributes' => [
                    'PrimeLineNo'      => $item['row_index'] ?? 1,
                    'QuantityToCancel' => $item['num'] ?? 1,
                    'Action'           => 'CANCEL',
                ],
                'Extn'        => [
                    '_attributes' => [
                        'ExtnRefundId' => $refund['refund_id'],
                    ],
                ],
            ];
            $orderLines[] = $orderLine;
        }

        return $orderLines;
    }
}
