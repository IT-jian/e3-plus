<?php


namespace App\Services\Hub\Adidas\Request\Transformer;


use App\Models\SysStdTradeItem;
use Illuminate\Support\Carbon;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * 预售订单完成 - 通过订单触发
 * 预售订单支付尾款，订单支付尾款之后触发
 *
 * Class StepTradePaidTransformer
 * @package App\Services\Hub\Adidas\Request\Transformer
 *
 * @author linqihai
 * @since 2020/1/6 15:10
 */
class StepTradePaidTransformer extends TradeCreateTransformer
{
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
                'EnterpriseCode'        => $trade['shop_code'],
                'OrderNo'               => $this->generatorOrderNo($trade['tid'], $trade['platform']),
                'DocumentType'          => '0001',
                'Override'              => 'Y',
                'PresalePmntNotifyDate' => Carbon::createFromTimeString($trade['created'])->toIso8601String(),
            ],
        ];
    }

    protected function getContent($trade)
    {

        $where = [
            'tid'      => $trade['tid'],
            'platform' => $trade['platform'],
        ];

        $orderLines = [];
        // 查询明细
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

        $content = [
            'Extn'           => [
                '_attributes' => [
                    'ExtnPresalePmntNotify' => 'Y',
                ],
            ],
            'OrderLines'     => [
                'OrderLine' => $orderLines,
            ],
            'PaymentMethods' => [
                'PaymentMethod' => [
                    '_attributes'    => [
                        'Action'            => 'Modify',
                        'Override'          => 'Y',
                        'PaymentType'       => 'PartnerPay', // 默认值
                        'PaymentReference2' => $trade['shop_code'],
                        'PaymentReference1' => $trade['shop_code'],
                    ],
                    'PaymentDetails' => [
                        '_attributes' => [
                            'TotalCharged'    => $trade['step_paid_fee'],
                            'AuthorizationID' => !empty($trade['pay_no']) ? $trade['pay_no'] : $trade['tid'],
                            'AuthAVS'         => !empty($trade['pay_no']) ? $trade['pay_no'] : $trade['tid'],
                            'ChargeType'      => 'CHARGE',
                        ],
                    ],
                ],
            ],
        ];

        // 订单级别优惠信息
        if ($tradePromotions) {
            $content['HeaderCharges']['HeaderCharge'] = $this->formatOrderPromotions($tradePromotions);
        }

        return $content;
    }

    protected function formatOrderLines($trade, $tradeItems, $tradePromotions = [])
    {
        $orderLines = [];
        $lineNo = 0;
        foreach ($tradeItems as $item) {
            $lineNo++;
            $promotions = isset($tradePromotions[$item['oid']]) ? $tradePromotions[$item['oid']] : [];
            $orderLinePromotions = $this->formatOrderLinePromotions($item, $promotions);
            $orderLine = [
                '_attributes' => [
                    'PrimeLineNo' => $item['row_index'],
                    'SubLineNo'   => $lineNo,
                    'Action'      => 'Modify',
                    'OrderedQty'  => $item['num'],
                    'Override'    => 'Y',
                ],
            ];
            if (!empty($orderLinePromotions)) {
                $orderLine['LineCharges']['LineCharge'] = $orderLinePromotions;
            }
            $orderLines[] = $orderLine;
        }

        return $orderLines;
    }
}