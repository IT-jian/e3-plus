<?php


namespace App\Services\Hub\Adidas\Request\Transformer;


use App\Models\SysStdRefundItem;
use App\Models\SysStdTradeItem;
use App\Models\SysStdTradePromotion;
use App\Services\Hub\Adidas\Request\Transformer\Traits\TradeCommonPartTrait;
use Illuminate\Support\Carbon;
use Spatie\ArrayToXml\ArrayToXml;

class RefundReturnCreateExtendTransformer extends BaseTransformer
{
    use TradeCommonPartTrait;

    private $extendStatus;
    private $extendTrade = [];

    public function format($stdRefund, $trade)
    {
        $rootElement = $this->getRootElement($stdRefund);

        $content = $this->getContent($stdRefund, $trade);

        return ArrayToXml::convert($content, $rootElement, false, 'UTF-8');
    }

    protected function getRootElement($refund)
    {
        return [
            'rootElementName' => 'createreturnorder',
        ];
    }

    protected function getContent($refund, $trade)
    {
        $where = [
            'refund_id' => $refund['refund_id'],
            'platform' => $refund['platform']
        ];
        $orderLines = [];
        // 查询明细
        $refundItems = SysStdRefundItem::where($where)->get();
        if (!empty($refundItems)) {
            $orderLines = $this->formatOrderLines($refund, $refundItems->toArray(), $trade);
        }
        $content = [
            'Order' => [
                '_attributes' => [
                    'DraftOrderFlag'      => 'Y',
                    'EnterpriseCode' => $refund['shop_code'],
                    'SellerOrganizationCode'   => $refund['shop_code'],
                    'DocumentType'   => '0001',
                    'OrderDate'      => Carbon::createFromTimeString($refund['created'])->toIso8601String(),
                    'CustomerPONo'   => $refund['refund_id'],
                    'OrderNo'        => '', // $this->generatorOrderNo($refund['tid'], $refund['platform']),
                    'EntryType'      => $this->entryTypeMap($refund['platform']),
                    'EnteredBy'      => 'Marketplace',
                ],
                'Extn'        => [
                    '_attributes' => [
                        'ExtnIsMigratedOrder'   => 'Y',
                        'ExtnFapiaoRequest'   => 'Y',
                        'ExtnStatus'        => $refund['status'],
                        'ExtnCustPONo'      => $refund['tid'],
                        'ExtnSellerOrgCode' => 'adidas',
                        'ExtnOrderNo'       => '', // $this->generatorOrderNo($refund['tid'], $refund['platform']),
                        'ExtnReturnOrderNo' => $this->generatorRefundNo($refund['refund_id'], $refund['platform']),
                    ],
                ],
                'PriceInfo'  => [
                    '_attributes' => [
                        'Currency' => 'CNY',
                        'EnterpriseCurrency' => 'CNY',
                    ],
                ],
                'OrderLines'  => [
                    'OrderLine' => $orderLines,
                ],
                'PaymentMethods' => $this->formatPaymentMethods($trade, $refund),
                'PersonInfoShipTo' => $this->shippingInfo($trade),
                'PersonInfoBillTo' => $this->shippingInfo($trade),
            ],
        ];

        return $content;
    }

    /**
     * 订单详情
     *
     * @param $refund
     * @param $refundItems
     * @param $trade
     * @return array
     *
     * @author linqihai
     * @since 2019/12/22 18:26
     */
    protected function formatOrderLines($refund, $refundItems, $trade)
    {
        $orderLines = [];
        if (empty($refundItems)) {
            return $orderLines;
        }
        foreach ($refundItems as $item) {
            $tradeItem = SysStdTradeItem::where('tid', $refund['tid'])->where('platform', $refund['platform'])->where('sku_id', $item['sku_id'])->first();
            if (empty($tradeItem)) { // 换了再退的sku查询不到，使用行号查询
                $tradeItem = SysStdTradeItem::where('tid', $refund['tid'])->where('platform', $refund['platform'])->where('row_index', $item['row_index'])->first();
            }
            $promotions = SysStdTradePromotion::where('tid', $refund['tid'])->where('platform', $refund['platform'])->where('id', $refund['oid'])->get();

            $itemId = $this->mapItemId($item['outer_sku_id']);
            $itemIdArr = explode('_', $itemId, 2);

            $orderLine = [
                '_attributes' => [
                    'SCAC'               => 'SF',
                    'ShipNode' => '',
                    'ReceivingNode' => '',
                    'OrderedQty'         => $item['num'],
                    'PrimeLineNo'      => $item['row_index'],
                    'LineType'      => ('step' == $trade['type']) ? 'Presale' : 'inline',
                    'Quantity'         => $item['num'],
                    'ReturnReasonCode' => $this->refundReasonCodeMap($item['reason'], $refund['platform']),
                    'ReturnReasonText' => $item['reason'],
                    'CustomerLinePONo' => empty($item['oid']) ? $item['row_index'] : $item['oid'],
                    // 'CarrierServiceCode' => 'STRD',
                ],
                'LineTaxes'        => [
                    'LineTax' => [
                        [
                            '_attributes' => [
                                'ChargeCategory' => 'LineTax',
                                'ChargeName'     => 'OrderLineTax',
                                'TaxName'        => 'OrderLineTax',
                                'Tax'            => '0.00',
                            ],
                        ],
                    ],
                ],
                'LinePriceInfo' => $this->linePriceInfo($tradeItem),
                'LineCharges' => [
                    'LineCharge' => $this->lineCharges(['trade' => $trade, 'item' => $tradeItem, 'promotions' => $promotions], $item['num']),
                ],
                'Extn'        => [
                    '_attributes' => [
                        'ExtnRefundId'         => $refund['refund_id'],
                        'ExtnEAN'              => $item['outer_sku_id'],
                        'ExtnCustItemID'       => $item['outer_iid'],
                        'ExtnRefundAmount'     => $this->getExtnRefundAmount($refund, $item, $tradeItem),
                        'ExtnSCAC'             => $this->getShippingName($refund),
                        'ExtnTrackingNo'       => $refund['sid'] ?? '',
                        'ExtnRefundPhase'      => 'aftersale',
                        'ExtnRefundVersion'    => $refund['refund_version'],
                        'ExtnArticleNumber'    => $itemIdArr[0] ?? "",
                        'ExtnLocalSizeCode'    => $tradeItem['size'],
                        'ExtnSizeCode'         => $itemIdArr[1] ?? "",
                    ],
                ],
                'Item'        => [
                    '_attributes' => [
                        'ItemID' => $this->mapItemId($item['outer_sku_id']),
                        // 'ItemShortDesc' => '',
                        'ItemDesc' => $tradeItem['title'],
                        'ProductClass' => 'NEW',
                        'ReturnItemID' => $item['outer_iid'],
                        'UPCCode' => '',
                        'UnitOfMeasure' => 'PIECE',
                    ],
                ],
                'PersonInfoShipTo' => $this->shippingInfo($trade),
            ];

            $orderLines[] = $orderLine;
        }

        return $orderLines;
    }

    /**
     * 天猫取退货单的refund_fee
     *
     * @param $refund
     * @param $refundItem
     * @param $tradeItem
     * @return mixed
     */
    public function getExtnRefundAmount($refund, $refundItem, $tradeItem)
    {
        return $refund['refund_fee'];
    }

    public function getShippingName($refund)
    {
        return $refund['company_name'];
    }

    public function formatPaymentMethods($trade, $refund)
    {
        return [
            'PaymentMethod' => [
                '_attributes'    => [
                    'CustomerPONo'      => $trade['tid'],
                    'PaymentType'       => 'PartnerPay', // 默认值
                    'PaymentReference2' => $trade['shop_code'],
                    'PaymentReference1' => $trade['shop_code'],
                    'PaymentReference3' => '',
                    'PaymentReference4' => $trade['shop_code'],
                    'PaymentReference9' => $trade['pay_no'] ? $trade['pay_no'] : $trade['tid'],
                ],
                'PaymentDetails' => [
                    '_attributes' => [
                        'RequestId'       => $trade['tid'],
                        'ChargeType'       => 'CHARGE',
                        // @todo 如何取值
                        'RequestAmount'   => $refund['refund_fee'] ?? '0.00',// ('step' == $trade['type'] ? $trade['step_paid_fee'] : $trade['payment']),
                        'AuthorizationID' => !empty($trade['pay_no']) ? $trade['pay_no'] : $trade['tid'],
                        'AuthAVS'         => !empty($trade['pay_no']) ? $trade['pay_no'] : $trade['tid'],
                    ],
                ],
            ],
        ];
    }

    public function lineCharges($params, $quantity = 1)
    {
        $tradeItemQuantity = $params['item']['num'] ?? 1;
        $lineCharges = (new TradeCreateTransformer())->formatOrderLinePromotions($params['item'], $params['promotions']);
        if ($tradeItemQuantity == $quantity) { // 数量相等不计算
            return $lineCharges;
        }
        foreach ($lineCharges as $key => $lineCharge) { // 计算均摊差额
            $lineCharges[$key]['_attributes']['ChargePerLine'] = round(($lineCharge['_attributes']['ChargePerLine'] / $tradeItemQuantity) * $quantity, 2);
        }

        return $lineCharges;
    }

}
