<?php


namespace App\Services\Hub\Adidas\Request\Transformer;


use App\Models\SysStdRefundItem;
use App\Models\SysStdTrade;
use App\Services\Hub\Adidas\Request\Transformer\Traits\TradeCommonPartTrait;
use Illuminate\Support\Carbon;
use Spatie\ArrayToXml\ArrayToXml;

class RefundReturnCreateTransformer extends BaseTransformer
{
    use TradeCommonPartTrait;

    public function format($stdRefund)
    {
        // 判断是否加强版报文
        if (cutoverTrade($stdRefund['tid'], $stdRefund['platform'])) {
            // 查询订单
            $where = [
                'tid' => $stdRefund['tid'],
                'platform' => $stdRefund['platform']
            ];
            $trade = SysStdTrade::where($where)->firstOrFail();
            return (new RefundReturnCreateExtendTransformer())->format($stdRefund, $trade);
        }

        $rootElement = $this->getRootElement($stdRefund);

        $content = $this->getContent($stdRefund);

        return ArrayToXml::convert($content, $rootElement, false, 'UTF-8');
    }

    protected function getRootElement($refund)
    {
        return [
            'rootElementName' => 'createreturnorder',
        ];
    }

    protected function getContent($refund)
    {
        $where = [
            'refund_id' => $refund['refund_id'],
            'platform' => $refund['platform']
        ];
        $orderLines = [];
        // 查询明细
        $refundItems = SysStdRefundItem::where($where)->get();
        if (!empty($refundItems)) {
            $orderLines = $this->formatOrderLines($refund, $refundItems->toArray());
        }
        $content = [
            'Order' => [
                '_attributes' => [
                    'EnterpriseCode' => $refund['shop_code'],
                    'DocumentType'   => '0001',
                    'OrderDate'      => Carbon::createFromTimeString($refund['created'])->toIso8601String(),
                    'OrderNo'        => $this->generatorOrderNo($refund['tid'], $refund['platform']),
                    'EntryType'      => $this->entryTypeMap($refund['platform']),
                    'CustomerPONo'   => $refund['refund_id'],
                ],
                'Extn'        => [
                    '_attributes' => [
                        'ExtnReturnOrderNo' => $this->generatorRefundNo($refund['refund_id'], $refund['platform']),
                        'ExtnStatus'        => $refund['status'],
                        'ExtnCustPONo'      => $refund['tid'],
                        'ExtnOrderNo'       => $this->generatorOrderNo($refund['tid'], $refund['platform']),
                    ],
                ],
                'OrderLines'  => [
                    'OrderLine' => $orderLines,
                ],
            ],
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
    protected function formatOrderLines($refund, $refundItems)
    {
        $orderLines = [];
        if (empty($refundItems)) {
            return $orderLines;
        }
        foreach ($refundItems as $item) {
            $orderLine = [
                '_attributes' => [
                    'PrimeLineNo'      => $item['row_index'],
                    'Quantity'         => $item['num'],
                    'ReturnReasonCode' => $this->refundReasonCodeMap($item['reason'], $refund['platform']),
                    'ReturnReasonText' => $item['reason'],
                    'CustomerLinePONo' => empty($item['oid']) ? $item['row_index'] : $item['oid'],
                ],
                'Extn'        => [
                    '_attributes' => [
                        'ExtnCustItemID'       => $item['outer_iid'],
                        'ExtnRefundAmount'     => $refund['refund_fee'],
                        'ExtnRefundId'         => $refund['refund_id'],
                        'ExtnSCAC'             => $this->getShippingName($refund),
                        'ExtnTrackingNo'       => $refund['sid'],
                        'ExtnRefundPhase'      => $refund['refund_phase'],
                        'ExtnRefundVersion'    => $refund['refund_version'],
                    ],
                ],
                'Item'        => [
                    '_attributes' => [
                        'ItemID' => $this->mapItemId($item['outer_sku_id']),
                    ],
                ],
            ];

            $orderLines[] = $orderLine;
        }

        return $orderLines;
    }

    public function getShippingName($refund)
    {
        return $refund['company_name'];
    }
}
