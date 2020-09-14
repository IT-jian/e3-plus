<?php


namespace App\Services\Hub\Adidas\Request\Transformer;


use App\Models\SysStdExchangeItem;
use App\Models\SysStdTradeItem;
use App\Models\SysStdTradePromotion;
use App\Services\Hub\Adidas\Request\Transformer\Traits\TradeCommonPartTrait;
use Illuminate\Support\Carbon;
use Spatie\ArrayToXml\ArrayToXml;

class ExchangeCreateExtendTransformer extends BaseTransformer
{
    use TradeCommonPartTrait;

    private $extendStatus;
    private $extendTrade = [];

    public function format($stdExchange, $trade)
    {
        $rootElement = $this->getRootElement($stdExchange);

        $content = $this->getContent($stdExchange, $trade);
        return ArrayToXml::convert($content, $rootElement, false, 'UTF-8');
    }

    protected function getRootElement($refund)
    {
        return [
            'rootElementName' => 'createreturnorder',
        ];
    }

    protected function getContent($exchange, $trade)
    {
        $where = [
            'dispute_id' => $exchange['dispute_id'],
            'platform' => $exchange['platform']
        ];
        // 查询明细
        $exchangeItems = SysStdExchangeItem::where($where)->get();
        $orders = [];
        // 组织 Order 数据
        $orders[] = $this->formatReturnOrder($exchange, $exchangeItems, $trade);
        $orders[] = $this->formatExchangeOrder($exchange, $exchangeItems, $trade);

        $content = [
            'Order' => $orders,
        ];

        return $content;
    }

    /**
     * 退货 order 格式
     *
     * @param $exchange
     * @param $items
     * @param $trade
     * @return array
     */
    protected function formatReturnOrder($exchange, $items, $trade)
    {
        $orderLines = $tradeItem = [];
        $defaultRowIndex = 0;
        $quantity = 0;
        foreach ($items as $item) {
            $tradeItem = SysStdTradeItem::where('tid', $exchange['tid'])->where('platform', $exchange['platform'])->where('sku_id', $item['bought_sku'])->first();
            $promotions = SysStdTradePromotion::where('tid', $exchange['tid'])->where('platform', $exchange['platform'])->where('id', $item['oid'])->get();

            $itemId = $this->mapItemId($item['bought_outer_sku_id']);
            $itemIdArr = explode('_', $itemId, 2);

            $defaultRowIndex++;
            $item['row_index'] = $defaultRowIndex;
            $orderLine = [
                '_attributes' => [
                    'LineType'         => ('step' == $trade['type']) ? 'Presale' : 'inline',
                    'SCAC'               => 'SF',
                    'ShipNode' => '',
                    'ReceivingNode' => '',
                    'OrderedQty'       => $item['num'],
                    'PrimeLineNo'      => $item['row_index'],
                    'Quantity'         => $item['num'],
                    'ReturnReasonCode' => $this->exchangeReasonCodeMap($item['reason'], $exchange['platform']),
                    'ReturnReasonText' => $item['reason'],
                    'CustomerLinePONo' => empty($item['oid']) ? $item['row_index'] : $item['oid'],
                    'CarrierServiceCode' => 'STRD',
                ],
                'LinePriceInfo' => $this->linePriceInfo($tradeItem),
                'LineCharges' => [
                    'LineCharge' => $this->lineCharges(['trade' => $trade, 'item' => $tradeItem, 'promotions' => $promotions], $item['num']),
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
                'Extn'        => [
                    '_attributes' => [
                        'ExtnEAN'              => '', // $tradeItem['outer_sku_id'],
                        'ExtnCustItemID'       => $tradeItem['outer_iid'],
                        'ExtnSCAC'             => $this->getShippingName($exchange),
                        'ExtnTrackingNo'       => $exchange['buyer_logistic_no'] ?? '',
                        'ExtnRefundPhase'      => 'aftersale',
                        'ExtnRefundVersion'    => $exchange['refund_version'],
                        'ExtnArticleNumber'    => $itemIdArr[0] ?? "",
                        'ExtnLocalSizeCode'    => $item['bought_size'],
                        'ExtnSizeCode'         => $itemIdArr[1] ?? "",
                    ],
                ],
                'Item'        => [
                    '_attributes' => [
                        'ItemDesc' => $tradeItem['title'] ?? '',
                        'ItemID' => $this->mapItemId($tradeItem['outer_sku_id']),
                        'ItemShortDesc' => '',
                        'ProductClass' => 'NEW',
                        'ReturnItemID' => $item['bought_outer_iid'],
                        'UPCCode' => '',
                        'UnitOfMeasure' => 'PIECE',
                    ],
                ],
                'PersonInfoShipTo' => $this->shippingInfo($trade),
            ];
            $quantity = $item['num'];
            $orderLines[] = $orderLine;
        }

        $content = [
            '_attributes' => [
                'DraftOrderFlag'      => 'Y',
                'EnterpriseCode' => $exchange['shop_code'],
                'DocumentType'   => '0001',
                'OrderDate'      => Carbon::createFromTimeString($exchange['created'])->toIso8601String(),
                'CustomerPONo'   => '',
                'OrderNo'        => '', // $this->generatorOrderNo($exchange['tid'], $exchange['platform']),
                'EntryType'      => $this->entryTypeMap($exchange['platform']),
                'EnteredBy'      => 'Marketplace',
            ],
            'Extn'        => [
                '_attributes' => [
                    'ExtnIsMigratedOrder'   => 'Y',
                    'ExtnFapiaoRequest'   => 'Y',
                    'ExtnStatus'        => $exchange['status'],
                    'ExtnSellerOrgCode' => 'adidas',
                    'ExtnCustPONo'      => $exchange['tid'],
                    'ExtnOrderNo'       => '',
                    'ExtnReturnOrderNo' => $this->generatorRefundNo($exchange['dispute_id'], $exchange['platform']),
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
            'PaymentMethods' => $this->formatPaymentMethods($trade, $tradeItem, $quantity),
            'PersonInfoShipTo' => $this->shippingInfo($trade),
            'PersonInfoBillTo' => $this->shippingInfo($trade),
        ];

        return $content;
    }

    /**
     * 换货单 shipping_info
     * @param $exchange
     * @param $trade
     * @return array
     */
    public function exchangeShippingInfo($exchange, $trade)
    {
        return (new ExchangeCreateTransformer())->shippingInfo($exchange);
    }

    public function getShippingName($exchange)
    {
        return $exchange['buyer_logistic_name'];
    }

    /**
     * 换货 order 格式
     * @param $exchange
     * @param $items
     * @param $trade
     * @return array
     */
    private function formatExchangeOrder($exchange, $items, $trade)
    {
        $orderLines = $tradeItem = [];
        $defaultRowIndex = 0;
        $quantity = 0;
        foreach ($items as $item) {
            $itemId = $this->mapItemId($item['exchange_outer_sku_id']);
            $itemIdArr = explode('_', $itemId, 2);
            $defaultRowIndex++;
            $item['row_index'] = $defaultRowIndex;

            $tradeItem = SysStdTradeItem::where('tid', $exchange['tid'])->where('platform', $exchange['platform'])->where('sku_id', $item['bought_sku'])->first();
            $promotions = SysStdTradePromotion::where('tid', $exchange['tid'])->where('platform', $exchange['platform'])->where('id', $item['oid'])->get();

            $orderLine = [
                '_attributes' => [
                    'LineType'         => ('step' == $trade['type']) ? 'Presale' : 'inline',
                    'OrderedQty'       => $item['num'],
                    'PrimeLineNo'      => $defaultRowIndex,
                    'CustomerLinePONo' => !empty($item['oid']) ? $item['oid'] : $defaultRowIndex,
                    'SCAC' => !empty($exchange['seller_logistic_name']) ? $item['seller_logistic_name'] : 'SF',
                    'CarrierServiceCode' => 'STRD',
                ],
                'Extn'        => [
                    '_attributes' => [
                        'ExtnArticleNumber' => $itemIdArr[0] ?? "",
                        'ExtnColor'         => $item['exchange_color'],
                        'ExtnDivisionCode'  => "",
                        'ExtnEAN'           => $item['exchange_outer_sku_id'],
                        'ExtnModelNo'       => "",
                        'ExtnSizeCode'      => $itemIdArr[1] ?? "",
                        'ExtnCustItemID'    => $item['exchange_outer_iid'],
                        'ExtnLocalSizeCode' => $item['exchange_size'],
                        'ExtnDisputeId'     => $exchange['dispute_id'],
                    ],
                ],
                'Item'        => [
                    '_attributes' => [
                        'ItemDesc'      => $item['exchange_title'],
                        'ItemID'        => $itemId,
                        // 'ItemShortDesc' => '',
                        'ProductClass'  => 'NEW',
                        'ReturnItemID'  => $item['bought_outer_iid'],
                        'UPCCode'       => '',
                        'UnitOfMeasure' => 'PIECE',
                    ],
                ],
                'LinePriceInfo' => $this->linePriceInfo($tradeItem),
                'LineCharges' => [
                    'LineCharge' => $this->lineCharges(['trade' => $trade, 'item' => $tradeItem, 'promotions' => $promotions], $item['num']),
                ],
                'LineTaxes'   => [
                    'LineTax' => [
                        [
                            '_attributes' => [
                                'ChargeCategory' => 'LineTax',
                                'ChargeName'     => 'OrderLineTax',
                                'TaxName'  => 'OrderLineTax',
                                'Tax'  => '0.00',
                            ],
                        ],
                    ],
                ],
                'PersonInfoShipTo' => $this->exchangeShippingInfo($exchange, $trade),
                // 'PersonInfoBillTo' => $this->shippingInfo($exchange),
            ];
            $quantity = $item['num'];
            $orderLines[] = $orderLine;
        }

        $content = [
            '_attributes'      => [
                'ExchangeType'    => 'REGULAR',
                'ReturnOrderHeaderKeyForExchange'    => '',
                'EnterpriseCode'    => $exchange['shop_code'],
                'SellerOrganizationCode'    => $exchange['shop_code'],
                'EntryType'    => $this->entryTypeMap($exchange['platform']),
                'EnteredBy'    => 'Marketplace',
                'OrderPurpose' => 'EXCHANGE',
                'OrderNo'      => $this->generatorExchangeNo($exchange['dispute_id'], $exchange['platform']),
                'CustomerPONo' => $exchange['dispute_id'],
                'OrderDate'    => Carbon::createFromTimeString($exchange['created'])->toIso8601String(),
                'CustomerContactID' => $trade['buyer_nick'] ?? '',
            ],
            'Extn'        => [
                '_attributes' => [
                    'ExtnIsMigratedOrder'          => 'Y',
                    'ExtnCustPONo'          => $exchange['tid'],
                    'ExtnOrderNo'           => '',
                ],
            ],
            'HeaderCharges'    => [],
            'HeaderTaxes'      => [],
            'OrderLines'       => [
                'OrderLine' => $orderLines,
            ],
            'PersonInfoShipTo' => $this->exchangeShippingInfo($exchange, $trade),
            'PersonInfoBillTo' => $this->exchangeShippingInfo($exchange, $trade),
            'PaymentMethods'   => $this->formatPaymentMethods($trade, $tradeItem, $quantity),
            'PriceInfo'        => [],
        ];

        return $content;
    }

    public function formatPaymentMethods($trade, $tradeItem, $exchangeNum)
    {
        $payment = 0 == $tradeItem['divide_order_fee'] ? $tradeItem['payment'] : $tradeItem['divide_order_fee'];
        if (empty($tradeItem['num'])) {
            $tradeItem['num'] = 1;
        }
        $exchangeNum = empty($exchangeNum) ? 1 : $exchangeNum;
        $requestAmount = round(($payment / $tradeItem['num']) * $exchangeNum, 2);

        return [
            'PaymentMethod' => [
                '_attributes'    => [
                    'CustomerPONo'      => $trade['tid'],
                    'PaymentType'       => 'PartnerPay', // 默认值
                    'PaymentReference2' => $trade['shop_code'],
                    'PaymentReference1' => $trade['shop_code'],
                    'PaymentReference3' => '',
                    'PaymentReference4' => $trade['shop_code'],
                    'PaymentReference9' => $trade['tid'],
                ],
                'PaymentDetails' => [
                    '_attributes' => [
                        'RequestId'       => $trade['tid'],
                        'ChargeType'       => 'CHARGE',
                        'RequestAmount'   => $requestAmount,
                        'AuthorizationID' => $trade['tid'],
                        'AuthAVS'         => $trade['tid'],
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
