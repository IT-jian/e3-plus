<?php


namespace App\Services\Hub\Adidas\Request\Transformer;


use App\Models\SysStdTradeItem;
use App\Models\SysStdTradePromotion;
use App\Services\Hub\Adidas\Request\Transformer\Traits\TradeCommonPartTrait;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * 组织订单创建数据格式
 *
 * Class TradeCreateTransformer
 * @package App\Services\Hub\Adidas\Request\Transformer
 *
 * @author linqihai
 * @since 2020/1/6 15:11
 */
class TradeCreateTransformer extends BaseTransformer
{
    use TradeCommonPartTrait;
    public function format($stdTrade)
    {
        $rootElement = $this->getRootElement($stdTrade);
        $content = $this->getContent($stdTrade);

        return ArrayToXml::convert($content, $rootElement, false, 'UTF-8');
    }

    protected function getRootElement($trade)
    {
        return [
            'rootElementName' => 'Order',
            '_attributes'     => [
                'EnterpriseCode'         => $trade['shop_code'],
                'SellerOrganizationCode' => $trade['shop_code'],
                'EntryType'              => $this->entryTypeMap($trade['platform']),
                'EnteredBy'              => 'Marketplace',
                'OrderDate'              => Carbon::createFromTimeString($trade['created'])->toIso8601String(),
                'DocumentType'           => '0001',
                'CustomerPONo'           => $trade['tid'],
                'OrderNo'                => $this->generatorOrderNo($trade['tid'], $trade['platform']),
                'CustomerEMailID'        => $trade['buyer_email'],
                'ValidateItem'           => 'N',
                'PaymentStatus'          => 'PAID',
                'OrderType'              => 'ShipToHome',
                'CustomerContactID'      => $trade['buyer_nick'],
                'CustomerFirstName'      => $trade['receiver_name'],
                'CustomerPhoneNo'        => $trade['receiver_mobile'] ? $trade['receiver_mobile'] : $trade['receiver_phone'],
                // 'PaymentUpdateNotify'    => $this->presaleOrderPaid($trade) ? 'Y' : 'N',
            ],
        ];
    }

    protected function getContent($trade)
    {
        $where = [
            'tid' => $trade['tid'],
            'platform' => $trade['platform']
        ];
        $orderLines = [];
        $itemsPromotions = $tradePromotions = [];
        /// 查询明细
        $tradeItems = SysStdTradeItem::where($where)->get();
        $promotionTypes = $this->getPromotions($trade);
        $itemsPromotions = $promotionTypes['itemsPromotions'];
        $tradePromotions = $promotionTypes['tradePromotions'];
        if (!empty($tradeItems)) {
            $oidArr = $tradeItems->pluck('oid')->unique()->toArray();
            if (count($oidArr) == 1 && in_array($trade['tid'], $oidArr)) { // 仅有一个子单号，则将优惠信息放在明细行
                if (isset($tradePromotions[$trade['tid']])) {
                    $itemsPromotions[$trade['tid']] = $tradePromotions[$trade['tid']];
                    unset($tradePromotions[$trade['tid']]);
                }
            }
            $orderLines = $this->formatOrderLines($trade, $tradeItems->toArray(), $itemsPromotions);
        }

        $content = $this->formatContent($trade, $orderLines);

        // 订单级别优惠信息
        if ($tradePromotions) {
            $content['HeaderCharges']['HeaderCharge'] = $this->formatOrderPromotions($tradePromotions);
        }
        // 运费
        $content['HeaderCharges']['HeaderCharge'][] = $this->formatPostFee($trade);

        $content['PersonInfoShipTo'] = $this->shippingInfo($trade);
        // 异常标识
        $orderHoldType = $this->markAsAbnormal($trade);
        if (!empty($orderHoldType)) {
            $content['OrderHoldTypes']['OrderHoldType'][] = $orderHoldType;
        } else {
            $content['BaisonHolds'] = [];
        }

        return $content;
    }

    public function formatContent($trade, $orderLines)
    {
        return [
            'Extn'             => [
                '_attributes' => [
                    'ExtnSellerFlag'        => $trade['seller_flag'],
                    'ExtnSellerMemo'        => $trade['seller_memo'],
                    'ExtnBuyMsg'            => $trade['buyer_message'],
                    'ExtnLocaleCode'        => 'zh_ZH',
                    'ExtnPaymentMethod'     => $this->payTypeMap($trade['pay_type']),
                    'ExtnOrderStatus'       => 'NEW',
                    'ExtnTaxCalculated'     => 'Y',
                    'ExtnCustPONo'          => $trade['tid'],
                    'ExtnOrderNo'           => $this->generatorOrderNo($trade['tid'], $trade['platform']),
                    'ExtnPresalePmntNotify' => 'N',
                    'ExtnStatus'            => $trade['status'],
                    'ExtnFapiaoRequest'     => 'N',
                    'ExtnSellerOrgCode'     => 'adidas',
                ],
            ],
            'PriceInfo'        => [
                '_attributes' => [
                    'Currency'           => 'CNY',
                    'EnterpriseCurrency' => 'CNY',
                ],
            ],
            'HeaderTaxes'      => [
                'HeaderTax' => [
                    '_attributes' => [
                        'ChargeCategory' => 'ShippingTax',
                        'ChargeName'     => 'ShippingTax_' . ('step' == $trade['type'] ? 'Presale' : 'Inline'),
                        'TaxName'        => 'ShippingTax_' . ('step' == $trade['type'] ? 'Presale' : 'Inline'),
                        'Tax'            => '0.00',
                    ],
                ],
            ],
            'OrderLines'       => [
                'OrderLine' => $orderLines,
            ],
            'PersonInfoBillTo' => $this->shippingInfo($trade),
            'PaymentMethods'   => $this->formatPaymentMethods($trade)
        ];
    }


    /**
     * 支付类型
     *
     * @param $pay
     * @return string
     *
     * @author linqihai
     * @since 2020/4/20 17:55
     */
    public function payTypeMap($pay)
    {
        return strtoupper($pay);
    }

    /**
     * 预售订单已经支付尾款
     *
     * @param $trade
     * @return bool
     *
     * @author linqihai
     * @since 2020/2/17 13:35
     */
    public function presaleOrderPaid($trade)
    {
        return isset($trade['step_paid_status']) && 'step' == $trade['type'] && 'FRONT_PAID_FINAL_PAID' == $trade['step_paid_status'];
    }

    /**
     * 订单行促销信息
     *
     * @param array $item
     * @param array $promotions
     * @return array
     *
     * @author linqihai
     * @since 2019/12/22 18:26
     */
    public function formatOrderLinePromotions($item, $promotions = [])
    {
        $formatPromotions = [];
        if ($item['discount_fee'] > 0) {
            $formatPromotions[] = [
                '_attributes' => [
                    'ChargeCategory' => 'TMP_MD_LINE',
                    'ChargeName'     => 'TMP_MD_LINE',
                    'ChargePerLine'  => $item['discount_fee'] ?? '0.00',
                ],
            ];
        }
        if ($item['part_mjz_discount'] > 0) {
            $formatPromotions[] = [
                '_attributes' => [
                    'ChargeCategory' => 'TMP_MD_HDR',
                    'ChargeName'     => 'TMP_MD_HDR',
                    'ChargePerLine'  => $item['part_mjz_discount'] ?? '0.00',
                ],
            ];
        }
        foreach ($promotions as $promotion) {
            $formatPromotions[] = [
                '_attributes' => [
                    'ChargeCategory' => 'TMP_MD_LINE_PROMO',
                    'ChargeName'     => $promotion['promotion_name'],
                    'ChargePerLine'  => $promotion['discount_fee'] ?? '0.00',
                ],
                'Extn'        => [
                    '_attributes' => [
                        'ExtnPromotionId' => $promotion['promotion_id'],
                        'ExtnCampaignId'  => $promotion['promotion_desc'],
                    ],
                ],
            ];
        }

        return $formatPromotions;
    }

    /**
     * 均摊订单级别的费用
     *
     * @param $couponFee
     * @param $tradeItems
     * @return array
     */
    public function formatItemCouponFee($couponFee, $tradeItems)
    {
        // 先除以100
        $couponFee = $couponFee / 100;
        $couponFeeMap = [];
        $totalOrderFee = 0;
        $leftCouponFee = $couponFee;
        $totalItem = count($tradeItems);
        if (1 == $totalItem) {
            $currentItem = current($tradeItems);
            $couponFeeMap[$currentItem['oid']] = $couponFee;
            return $couponFeeMap;
        }
        if ($couponFee > 0) {
            foreach ($tradeItems as $tradeItem) {
                $totalOrderFee += $tradeItem['divide_order_fee'];
            }
            if ($totalOrderFee <= 0) {
                return $couponFeeMap;
            }
            $index = 0;
            foreach ($tradeItems as $item) {
                $index++;
                $divideCouponFee = round($couponFee * ($item['divide_order_fee'] / $totalOrderFee), 2);
                if ($index == $totalItem) { // 最后一个直接赋值
                    $divideCouponFee = round($divideCouponFee, 2);
                }
                if ($divideCouponFee > 0) {
                    $couponFeeMap[$item['oid']] = $divideCouponFee;
                }
                $leftCouponFee -= $divideCouponFee;
            }
        }

        return $couponFeeMap;
    }

    /**
     * 天猫 coupon_fee 处理
     *
     * @param $couponFee
     * @return array
     */
    public function formatOrderLineCouponFeePromotion($couponFee)
    {
        return [
            '_attributes' => [
                'ChargeCategory' => 'TMP_MD_LINE_PROMO',
                'ChargeName'     => 'TMALL_COUPON_FEE',
                'ChargePerLine'  => $couponFee ?? '0.00',
            ],
            'Extn'        => [
                '_attributes' => [
                    'ExtnPromotionId' => 'TMALL_COUPON_FEE',
                    'ExtnCampaignId'  => 'TMALL_COUPON_FEE',
                ],
            ],
        ];
    }

    /**
     * 订单行优惠
     *
     * @param array $tradePromotions
     * @return array
     *
     * @author linqihai
     * @since 2019/12/23 20:00
     */
    protected function formatOrderPromotions($tradePromotions = [])
    {
        $formatPromotions = [];
        foreach ($tradePromotions as $tid => $promotions) {
            foreach ($promotions as $promotion) {
                $formatPromotions[] = [
                    '_attributes' => [
                        'ChargeCategory' => 'TMP_MD_HDR_PROMO',
                        'ChargeName'     => $promotion['promotion_desc'],
                        'ChargeAmount'   => $promotion['discount_fee'] ?? '0.00',
                    ],
                    'Extn'        => [
                        '_attributes' => [
                            'ExtnPromoId'    => $promotion['promotion_id'],
                            'ExtnCampaignId' => $promotion['promotion_desc'],
                        ],
                    ],
                ];
            }
        }

        return $formatPromotions;
    }

    /**
     * 运费格式化
     *
     * @param $trade
     * @return array
     *
     * @author linqihai
     * @since 2020/1/10 14:51
     */
    protected function formatPostFee($trade)
    {
        return [
            '_attributes' => [
                'ChargeCategory' => 'ShippingCharge',
                'ChargeName'     => 'Shipping_' . ('step' == $trade['type'] ? 'Presale' : 'Inline'),
                'ChargeAmount'   => $trade['post_fee'] ?? '0.00',
            ],
        ];
    }

    /**
     * 订单详情
     *
     * @param $trade
     * @param $tradeItems
     * @param array $tradePromotions
     * @return array
     *
     * @author linqihai
     * @since 2019/12/22 18:26
     */
    protected function formatOrderLines($trade, $tradeItems, $tradePromotions = [])
    {
        $orderLines = [];

        // 订单中使用红包付款的金额 coupon_fee 计算分摊
        $itemCouponFeeMap = [];
        if ($trade['coupon_fee'] > 0) {
            $itemCouponFeeMap = $this->formatItemCouponFee($trade['coupon_fee'], $tradeItems);
        }
        foreach ($tradeItems as $item) {
            $promotions = isset($tradePromotions[$item['oid']]) ? $tradePromotions[$item['oid']] : [];
            if ('Y' == $this->giftFlag($item)) {
                $item['price'] = 0;
                $item['discount_fee'] = 0;
            }
            $orderLinePromotions = $this->formatOrderLinePromotions($item, $promotions);
            // 处理优惠字段
            if (isset($itemCouponFeeMap[$item['oid']])) {
                $orderLinePromotions[] = $this->formatOrderLineCouponFeePromotion($itemCouponFeeMap[$item['oid']]);
            }
            $orderLine = $this->formatOrderLine($trade, $item);
            $orderLine['_attributes']['GiftFlag'] = $this->giftFlag($item);
            if ('Y' == $orderLine['_attributes']['GiftFlag']) { // 赠品GWP的处理
                $orderLine['Extn']['_attributes']['ExtnColor'] = '';
                $orderLine['Extn']['_attributes']['ExtnLocalSizeCode'] = '';
                $orderLine['Extn']['_attributes']['ExtnSizeCode'] = '';
                $orderLine['Extn']['_attributes']['ExtnEAN'] = $item['outer_iid'];
                $orderLine['Extn']['_attributes']['ExtnArticleNumber'] = $item['outer_iid'];
                $orderLine['Item']['_attributes']['ItemID'] = $item['outer_iid'];
            }
            if (!empty($orderLinePromotions)) {
                $orderLine['LineCharges']['LineCharge'] = $orderLinePromotions;
            }
            $orderLines[] = $orderLine;
        }

        return $orderLines;
    }

    public function formatOrderLine($trade, $item)
    {
        $itemId = $this->mapItemId($item['outer_sku_id']);
        $itemIdArr = explode('_', $itemId, 2);
        $orderLine = [
            '_attributes'      => [
                'PrimeLineNo'        => $item['row_index'],
                'OrderedQty'         => $item['num'],
                'LineType'           => ('step' == $trade['type']) ? 'Presale' : 'inline',
                'SCAC'               => 'SF',
                'CarrierServiceCode' => 'STRD',
                'CustomerLinePONo'   => empty($item['oid']) ? $item['row_index'] : $item['oid'],
                // 'GiftFlag'           => $this->giftFlag($promotions),
            ],
            'Extn'             => [
                '_attributes' => [
                    'ExtnEAN'           => $item['outer_sku_id'],
                    'ExtnCustItemID'    => $item['outer_iid'],
                    'ExtnColor'         => $item['color'] ?? '',
                    'ExtnArticleNumber' => $itemIdArr[0] ?? '',
                    'ExtnSizeCode'      => $itemIdArr[1] ?? '',
                    'ExtnLocalSizeCode' => $item['size'] ?? '',
                ],
            ],
            'Item'             => [
                '_attributes' => [
                    'UPCCode'       => '',
                    'ItemDesc'      => $item['title'] ?? '',
                    'ItemID'        => $itemId, // 赠品 取 outer_id
                    'ProductClass'  => 'NEW',
                    'UnitOfMeasure' => 'PIECE',
                ],
            ],
            'LinePriceInfo'    => $this->linePriceInfo($item),
            'PersonInfoShipTo' => $this->shippingInfo($trade),
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
        ];

        return $orderLine;
    }

    /**
     * 标识为异常订单
     *
     * @param $trade
     * @return array
     *
     * @author linqihai
     * @since 2020/2/17 13:54
     */
    public function markAsAbnormal($trade)
    {
        // 省份映射表异常
        if (!$this->stateMap($trade['receiver_state'])) {
            return $this->formatAbnormalLines();
        }
        // 不为空字段
        $requiredFields = [
            'receiver_state', 'receiver_city',
            'receiver_address', 'receiver_name',
        ];
        if (empty($trade['receiver_mobile']) && empty($trade['receiver_phone'])) {
            return $this->formatAbnormalLines();
        }
        foreach ($requiredFields as $field) { // 不为空
            $value = trim($trade[$field]);
            if (empty($value)) {
                return $this->formatAbnormalLines();
            }
        }

        if ('taobao' == $trade['platform']) {
            if (Str::length($trade['receiver_address'], 'UTF-8') > 200) {
                return $this->formatAbnormalLines();
            }
        }

        return [];
    }

    public function formatAbnormalLines($reason = 'Address Invalid Hold', $type = 'ADDRESS_INVALID_HOLD')
    {
        return [
            '_attributes' => [
                'ReasonText'     => $reason,
                'ResolverUserId' => '',
                'HoldType'       => $type,
            ],
        ];
    }

    /**
     * 订单促销分类 - 分为明细和主单
     *
     * @param $trade
     * @return array
     *
     * @author linqihai
     * @since 2020/4/26 20:39
     */
    protected function getPromotions($trade)
    {

        $itemsPromotions = $tradePromotions = [];

        $where = [
            'tid'      => $trade['tid'],
            'platform' => $trade['platform'],
        ];
        // 查询优惠
        $allPromotions = SysStdTradePromotion::where($where)->orderBy('discount_fee', 'desc')->get(); // 降序
        if (!empty($allPromotions)) { // 处理整单优惠还是明细优惠
            foreach ($allPromotions as $tradePromotion) {
                if ($tradePromotion['tid'] == $tradePromotion['id']) { // 优惠id == tid，表示为主单优惠信息
                    if (!isset($tradePromotions[$tradePromotion['id']])) {
                        $tradePromotions[$tradePromotion['id']] = [];
                    }
                    $tradePromotions[$tradePromotion['id']][] = $tradePromotion->toArray();
                } else {
                    if (!isset($itemsPromotions[$tradePromotion['id']])) {
                        $itemsPromotions[$tradePromotion['id']] = [];
                    }
                    $itemsPromotions[$tradePromotion['id']][] = $tradePromotion->toArray();
                }
            }
        }

        return compact('itemsPromotions', 'tradePromotions');
    }
}
