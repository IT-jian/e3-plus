<?php


namespace App\Services\Hub\Adidas\Request\Transformer;


use App\Models\SysStdRefundItem;
use Spatie\ArrayToXml\ArrayToXml;

class RefundCreateTransformer extends BaseTransformer
{
    public function format($stdRefund)
    {
        $where = [
            'refund_id' => $stdRefund['refund_id'],
            'platform' => $stdRefund['platform']
        ];
        // 查询明细
        $refundItems = SysStdRefundItem::where($where)->get()->toArray();
        $rootElement = $this->getRootElement($stdRefund, $refundItems);
        $content = $this->getContent($stdRefund, $refundItems);

        return ArrayToXml::convert($content, $rootElement, false, 'UTF-8');
    }

    private function getRootElement($refund, $refundItems)
    {
        // 查询明细
        $item = current($refundItems);

        return [
            'rootElementName' => 'OrderInvoice',
            '_attributes'     => [
                'AdjustmentReason' => $this->refundReasonCodeMap($item['reason'], $refund['platform']),
                'CustomerPONo'     => $refund['tid'],
                'OrderNo'          => $this->generatorOrderNo($refund['tid'], $refund['platform']),
                'EntryType'        => $this->entryTypeMap($refund['platform']),
                'RefundFull'       => '',
            ],
        ];
    }

    private function getContent($refund, $refundItems)
    {
        $orderLines = $headLines = [];
        // 查询明细
        if (!empty($refundItems)) {
            $orderLines = $this->formatOrderLines($refund, $refundItems);
            $headLines = $this->headerLines($refund, $refundItems);
        }
        $content = [
            'Extn' => [
                '_attributes' => [
                    'ExtnReturnOrderNo' => $this->generatorRefundNo($refund['refund_id'], $refund['platform']),
                ],
            ],
            'LineDetails' => [
                'LineDetail' => $orderLines
            ],
            'HeaderChargeList' => [
                'HeaderCharge' => $headLines
            ]
        ];

        return $content;
    }

    /**
     * 订单详情
     *
     * @param $refund
     * @param $refundItems
     * @return array
     *
     * @author linqihai
     * @since 2019/12/22 18:26
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
                    'PrimeLineNo'      => $item['row_index'],
                    'AdjustmentReason' => $this->refundReasonCodeMap($item['reason'], $refund['platform']),
                    'Quantity'         => $item['num'],
                    'ItemID'           => '',
                ],
                'Extn'        => [
                    '_attributes' => [
                        'ExtnRefundId'         => $refund['refund_id'],
                        'ExtnRefundPhase'      => $refund['refund_phase'] ?? '',
                        'ExtnRefundVersion'    => $refund['refund_version'] ?? '',
                    ],
                ],
                'LineChargeList'        => [
                    'LineCharge' => [
                        '_attributes' => [
                            'ChargeCategory' => '',
                            'ChargeName' => '',
                            'ChargePerLine' => '',
                        ],
                    ],
                ],
                'LineTaxList'        => [
                    'LineTax' => [
                        '_attributes' => [
                            'Tax' => '',
                        ],
                    ],
                ],
            ];
            $orderLines[] = $orderLine;
        }

        return $orderLines;
    }

    private function headerLines($refund, $refundItems)
    {
        $orderLines = [];
        $orderLines[] = [
            '_attributes' => [
                'ChargeAmount'   => $refund['refund_fee'], // 退款金额
                'ChargeCategory' => 'Appeasement',
                'ChargeName'     => 'SpecialEvent',
            ],
        ];
        return $orderLines;
    }
}