<?php


namespace App\Services\Hub\Adidas\JingdongRequest\Transformer;


use App\Models\JingdongOrderSplitAmount;
use App\Models\JingdongTrade;
use App\Models\SysStdTradeItem;
use App\Services\Hub\Adidas\Request\Transformer\TradeCreateTransformer as BaseTransformer;

/**
 * 组织京东订单创建数据格式
 *
 * Class TradeCreateTransformer
 * @package App\Services\Hub\Adidas\JingdongRequest\Transformer
 */
class TradeCreateTransformer extends BaseTransformer
{
    const PROMOTION_TYPE_SHOP_SHARE = 'shop_share'; // 商家承担优惠
    const PROMOTION_TYPE_PLATFORM_SHARE = 'platform_share'; // 平台承担优惠

    // 原始报文信息
    protected $originContent = [];

    public $promotionTypes = [
        1  => '京券',
        2  => '东券',
        3  => '店铺京券',
        4  => '店铺东券',
        5  => '限品类京券',
        6  => '限品类东券',
        7  => '全品类免运费京券',
        8  => '店铺限商品京劵类型',
        9  => '店铺限商品东劵类型',
        10 => '限品类运费京劵',
        11 => '自营FBP联合优惠券',
        12 => '自营FBP联合按比例承担优惠劵',
        13 => '自营FBP联合按比例承担优惠劵（自营承担）',
        14 => '自营FBP联合按比例承担优惠劵（商家承担）',

        101 => '礼品卡',
        110 => '余额',
        111 => '手机红包',
        112 => '在线支付',
        113 => '现金',
        114 => '支票',
        115 => 'POS',
        116 => '转账',
        117 => '邮局汇款',
        118 => '电话分期',
        119 => '积分',
        120 => '返积分',
        121 => '反对账',
        122 => 'EPT优惠码优惠',
        123 => '京豆',
        124 => '价保返回的余额',
        125 => '电汇',
        126 => '支付营销优惠类型',
        127 => '价保优惠',
        128 => '钢镚优惠',
        129 => '提货卡',
        130 => '自营续重运费调整减项',
        131 => '售后赔付前置',
        132 => '生鲜续重运费调整减项',
        133 => '福利积分',
        134 => '中移动和包支付优惠',
        135 => '杉德卡',
        136 => '纽海礼品卡',
        137 => '泰州宿迁项目医保支付',
        138 => '员工卡支付',
        139 => '钱包余额支付',
        140 => '领货码',
        141 => '活动金',
        142 => '礼金优惠',
        143 => '沃尔玛礼品卡',
        144 => '唯品会支付',
        145 => '唯品会微信支付',
        146 => '唯品会唯品币支付',
        147 => '唯品会红包支付',
        148 => '承兑支付',
        149 => '品牌京采豆',
        150 => '品类京采豆',
        151 => '超级红包支付',
        152 => '美京豆支付',
        153 => '门店现金支付',
        154 => '金币支付',
        155 => '门店刷卡支付',
        156 => '京东支付优惠',
        157 => '银承余额支付',
        160 => 'plus会员优惠',

        201 => '套装优惠',
        202 => '单品优惠',
        203 => '闪团',
        204 => '团购',
        205 => '赠品促销',
        206 => '满赠满返促销',
        207 => '封顶',
        208 => '笔记本以旧换新',
        209 => '满赠优惠',
        210 => '加价购优惠',
        211 => '预售优惠',
        212 => 'SKU限购优惠',
        213 => '节能补贴优惠',
        309 => '优惠劵 YHD承担(商城平台券+我的红包)',
        310 => '优惠劵 JD承担 (商家店铺券)',
        311 => '促销 各类促销分摊总额 (不包括券金额)',
        312 => '一号店余额',
        313 => '一号店运费',
        320 => '唯品会优惠劵优惠',
        321 => '唯品会促销优惠',
        322 => '唯品会支付优惠',

        401 => '落地配服务费',
        402 => '全球购税费',
        413 => '退换无忧服务费',
        431 => 'vop出票服务费',
        433 => '跨区发货服务费',
        434 => '铁路客票代收费',
        435 => 'POP租赁-租金',
        436 => 'POP租赁-押金',
        437 => '跨境电商综合税（包税)',
        442 => 'POP汽车增值服务费',
        443 => '集运服务费',
        444 => '京尊达服务费',
        448 => '循环包装服务费',
        450 => '全球售GST税费',
        454 => '大客户未税价税费',

        404 =>'普通运费',
        405 =>'特殊运费',
        406 =>'自提运费',
        407 =>'极速达运费',
        408 =>'移动仓运费',
        409 =>'小件京准达运费',
        410 =>'大件京准达运费',
        415 =>'自营续重运费',
        416 =>'生鲜基础运费',
        417 =>'生鲜自提运费',
        418 =>'生鲜续重运费',
        419 =>'生鲜中小件京准达运费',
        420 =>'生鲜极速达运费',
        421 =>'全球售普通运费',
        422 =>'全球售续重运费',
        423 =>'全球售燃油附加运费',
        432 =>'大件移动仓极速达运费',
        438 =>'非生鲜大件极速达运费',
        439 =>'生鲜大件极速达运费',
        440 =>'非生鲜光速达运费',
        441 =>'生鲜光速达运费',
        445 =>'前置仓普通运费',
        446 =>'前置仓续重运费',
        447 =>'中小件光速达运费',
        449 =>'全球售自提运费',
        451 =>'全球售图书运费',
        452 =>'全球售自提图书运费',
        430 =>'pop基础运费',
        501 =>'虚拟sku和真实sku对应关系(code值为虚拟sku的id，amount金额为0)',
    ];

    protected function getRootElement($trade)
    {
        $rootElement = parent::getRootElement($trade);
        // 查询京东订单原报文
        $jingdongTrade = JingdongTrade::where('order_id', $trade['tid'])->first();
        $this->originContent = $jingdongTrade['origin_content'] ?? [];

        return $rootElement;
    }

    protected function getContent($trade)
    {
        if (empty($this->originContent)) {
            // 查询京东订单原报文
            $jingdongTrade = JingdongTrade::where('order_id', $trade['tid'])->first();
            $this->originContent = $jingdongTrade['origin_content'] ?? [];
        }

        $where = [
            'tid' => $trade['tid'],
            'platform' => $trade['platform']
        ];
        $orderLines = [];
        $itemsPromotions = $tradePromotions = [];
        /// 查询明细
        $tradeItems = SysStdTradeItem::where($where)->get();

        $itemsPromotions = $this->getItemsPromotions($trade);
        if (!empty($tradeItems)) {
            $orderLines = $this->formatOrderLines($trade, $tradeItems->toArray(), $itemsPromotions);
        }
        // 主体
        $content = $this->formatContent($trade, $orderLines);
        // 父订单ID
        $parentOrderId = $this->originContent['directParentOrderId'] ?? '';
        $content['Extn']['_attributes']['ExtnParentOrderNo'] = $parentOrderId ? $this->generatorOrderNo($parentOrderId, $trade['platform']) : '';
        // 发票信息
        $fapiaoLines = $this->formatFapiaoLines($trade);
        if ($fapiaoLines) {
            $content['Extn']['ADSHeaderDetailsList'] = $fapiaoLines;
            $content['Extn']['_attributes']['ExtnFapiaoRequest'] = 'Y';
        }
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

    /**
     * 京东订单优惠分摊详情
     *
     * @param $trade
     * @return array
     */
    public function getItemsPromotions($trade)
    {
        $itemsPromotions = [];
        // 查京东均摊金额
        $orderSplitAmount = JingdongOrderSplitAmount::where('order_id', $trade['tid'])->first();
        if (!empty($orderSplitAmount)) {
            $orderItemsAmount = $orderSplitAmount->origin_content;
            foreach ($orderItemsAmount as $item) {
                $itemsPromotions[$item['skuId']] = $item;
            }
        }

        return $itemsPromotions;
    }

    protected function formatOrderLines($trade, $tradeItems, $tradePromotions = [])
    {
        $orderLines = [];
        foreach ($tradeItems as $item) {
            $promotions = isset($tradePromotions[$item['sku_id']]) ? $tradePromotions[$item['sku_id']] : [];
            $orderLinePromotions = $this->formatOrderLinePromotions($item, $promotions);
            $orderLine = $this->formatOrderLine($trade, $item);
            // $orderLine['_attributes']['GiftFlag'] = '';
            if (!empty($orderLinePromotions)) {
                $orderLine['LineCharges']['LineCharge'] = $orderLinePromotions;
            }
            $orderLines[] = $orderLine;
        }

        return $orderLines;
    }

    public function payTypeMap($pay)
    {
        $payMethod =  explode('-', $pay);
        $map = [
            1 => 'cash on delivery',  // 货到付款
            2 => 'post office remittance',  // 邮局汇款
            3 => 'Self mention',  // 自提
            4 => 'pay online',  // 在线支付
            5 => 'Company transfer',  // 公司转账
            6 => 'bank card transfer',  // 银行卡转账
        ];

        return $map[$payMethod[0]] ?? $pay;
    }

    public function formatFapiaoLines($trade)
    {
        if (empty($this->originContent)) {
            // 查询京东订单原报文
            $jingdongTrade = JingdongTrade::where('order_id', $trade['tid'])->first();
            $this->originContent = $jingdongTrade['origin_content'] ?? [];
        }
        $originContent = $this->originContent;
        // invoiceEasyInfo.invoiceType = 0 表示不开票
        if (empty(data_get($originContent, 'vatInfo.vatNo', '')) && empty(data_get($originContent, 'invoiceEasyInfo.invoiceType', ''))) {
            return [];
        }
        $vatInfo = data_get($originContent, 'vatInfo', []);
        $invoiceEasyInfo = data_get($originContent, 'invoiceEasyInfo', []);
        $fapiao = [
            'InvoiceType'           => $invoiceEasyInfo['invoiceType'] ?? '',
            'InvoiceTitle'          => $invoiceEasyInfo['invoiceTitle'] ?? '',
            'InvoiceContentId'      => $invoiceEasyInfo['invoiceContentId'] ?? '',
            'InvoiceConsigneeEmail' => $invoiceEasyInfo['invoiceConsigneeEmail'] ?? '',
            'InvoicePhone'          => $invoiceEasyInfo['invoiceConsigneePhone'] ?? '',
            'InvoiceCode'           => $invoiceEasyInfo['InvoiceCode'] ?? '',
            'TaxPayerId'            => $vatInfo['vatNo'] ?? '',
            'CompanyAddress'        => $vatInfo['addressRegIstered'] ?? '',
            'CompanyPhone'          => $vatInfo['phoneRegIstered'] ?? '',
            'accountbankname'       => $vatInfo['depositBank'] ?? '',
            'accountbanknumber'     => $vatInfo['bankAccount'] ?? '',
            'InvoiceAddress'        => $vatInfo['userAddress'] ?? '',
            'UserName'              => $vatInfo['userName'] ?? '',
            'UserPhone'             => $vatInfo['UserPhone'] ?? '',
            'InvoicePerson'         => $vatInfo['invoicePersonalName'] ?? '',
        ];

        $ADSHeaderDetailsList = [];
        foreach ($fapiao as $field => $value) {
            $ADSHeaderDetailsList['ADSHeaderDetails'][] = [
                '_attributes' => [
                    'CustomAttributeHeader' => 'custom-attributes',
                    'CustomAttributeKey'    => $field, // 默认值
                    'CustomAttributeValue'  => $value,
                ],
            ];
        }

        return $ADSHeaderDetailsList;
    }

    /**
     * 商品优惠信息
     *
     * @param array $item
     * @param array $promotions
     * @return array
     */
    public function formatOrderLinePromotions($item, $promotions = [])
    {
        $formatPromotions = $promotionTypeMap = $typeMap = [];
        if (!empty($promotions)) {
            $promotionLines = $promotions['amountExpands'];
            foreach ($promotionLines as $promotionLine) {
                $promotionTypeMap[$promotionLine['type']][] = $promotionLine;
            }
            foreach ($promotionTypeMap as $type => $linePromotions) {
                $typeMap[$type]= $this->formatPromotion($linePromotions);
                if ($this->isShopPromotion($type)) {
                    $typeMap[$type]= $this->formatPromotion($linePromotions);
                } else if ($this->isPlatformPromotion($type)) {
                    $typeMap[$type][] = $this->formatPromotion($linePromotions);
                }
            }
            foreach ($typeMap as $type => $promotion) {
                if ($this->isShopPromotion($type)) {
                    $formatPromotions[] = [
                        '_attributes' => [
                            'ChargeCategory' => 'TMP_MD_LINE',
                            'ChargeName'     => $this->promotionTypes[$type] ?? $type,
                            'ChargePerLine'  => $promotion['amount'] ?? '0.00',
                        ],
                        'Extn' => [
                            '_attributes' => [
                                'ExtnCampaignId' => 'Adidas_Coupon',
                                'ExtnPromotionId' => $promotion['code'],
                            ]
                        ]
                    ];
                } else if ($this->isPlatformPromotion($type)) {
                    $formatPromotions[] = [
                        '_attributes' => [
                            'ChargeCategory' => 'TMP_MD_LINE_PROMO',
                            'ChargeName'     => $this->promotionTypes[$type] ?? $type,
                            'ChargePerLine'  => $promotion['amount'] ?? '0.00',
                        ],
                        'Extn' => [
                            '_attributes' => [
                                'ExtnCampaignId' => 'JD_Coupon',
                                'ExtnPromotionId' => $promotion['code'],
                            ]
                        ]
                    ];
                }
            }

            // 商家承担： 满减金额 + 返现金额
            if (isset($promotions['moneyOfSuit']) && $promotions['moneyOfSuit']) {
                $formatPromotions[] = [
                    '_attributes' => [
                        'ChargeCategory' => 'TMP_MD_LINE',
                        'ChargeName'     => '满减',
                        'ChargePerLine'  => $promotions['moneyOfSuit'] ?? '0.00',
                    ],
                    'Extn' => [
                        '_attributes' => [
                            'ExtnCampaignId' => 'Adidas_Coupon',
                            'ExtnPromotionId' => $promotions['promoId'],
                        ]
                    ]
                ];
            }
            // 返现
            if (isset($promotions['rePrice']) && $promotions['rePrice']) {
                $formatPromotions[] = [
                    '_attributes' => [
                        'ChargeCategory' => 'TMP_MD_LINE',
                        'ChargeName'     => '返现',
                        'ChargePerLine'  => $promotions['rePrice'] ?? '0.00',
                    ],
                    'Extn' => [
                        '_attributes' => [
                            'ExtnCampaignId' => 'Adidas_Coupon',
                            'ExtnPromotionId' => $promotions['promoId'],
                        ]
                    ]
                ];
            }

            // 平台承担 礼品卡优惠
            if (isset($promotions['giftCardDiscount']) && $promotions['giftCardDiscount']) {
                $formatPromotions[] = [
                    '_attributes' => [
                        'ChargeCategory' => 'TMP_MD_LINE_PROMO',
                        'ChargeName'     => '礼品卡总优惠',
                        'ChargePerLine'  => $promotions['giftCardDiscount'],
                    ],
                    'Extn' => [
                        '_attributes' => [
                            'ExtnCampaignId' => 'JD_Coupon',
                            'ExtnPromotionId' => $promotions['promoId'],
                        ]
                    ]
                ];
            }
            // 手机红包
            if (isset($promotions['mobileDiscount']) && $promotions['mobileDiscount']) {
                $formatPromotions[] = [
                    '_attributes' => [
                        'ChargeCategory' => 'TMP_MD_LINE_PROMO',
                        'ChargeName'     => '手机红包',
                        'ChargePerLine'  => $promotions['mobileDiscount'],
                    ],
                    'Extn' => [
                        '_attributes' => [
                            'ExtnCampaignId' => 'JD_Coupon',
                            'ExtnPromotionId' => $promotions['promoId'],
                        ]
                    ]
                ];
            }

            foreach ($formatPromotions as $key => $promotion) {
                $formatPromotions[$key]['_attributes']['ChargePerLine'] = sprintf('%.2f', $promotion['_attributes']['ChargePerLine']);
            }
        }

        return $formatPromotions;
    }

    /**
     * 合计京东明细金额
     * @param $linePromotions
     * @return mixed
     */
    public function formatPromotion($linePromotions)
    {
        if (1 == count($linePromotions)) {
            return current($linePromotions);
        }

        // 合计金额
        $amount = array_sum(array_column($linePromotions, 'amount'));
        $promotion = array_shift($linePromotions);
        $promotion['amount'] = $amount;

        return $promotion;
    }
    /**
     * 是否商家承担优惠类型
     *
     * @param $type
     * @return bool
     */
    public function isShopPromotion($type)
    {
        return in_array($type, [
                3, 4, 5, 8, 9, 14, 201, 202, 205, 209, 211, 119, 122, 131, 142, 149, 11, 160
        ]);
    }

    /**
     * 是否平台承担优惠类型
     *
     * @param $type
     * @return bool
     */
    public function isPlatformPromotion($type)
    {
        return in_array($type, [
            1, 2, 6, 13, 123, 126, 128, 133, 138, 150, 151, 156
        ]);
    }
}
