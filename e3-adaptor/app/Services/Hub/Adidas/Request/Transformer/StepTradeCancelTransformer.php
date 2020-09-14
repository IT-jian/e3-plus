<?php


namespace App\Services\Hub\Adidas\Request\Transformer;


use App\Models\SysStdTradeItem;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * 预售订单取消 - 通过订单触发
 * 预售订单未支付尾款，订单关闭之后触发
 *
 * Class TradeCancelTransformer
 * @package App\Services\Hub\Adidas\Request\Transformer
 *
 * @author linqihai
 * @since 2020/1/6 15:10
 */
class StepTradeCancelTransformer extends TradeCancelTransformer
{
    public function format($stdTrade)
    {

        $where = [
            'tid'      => $stdTrade['tid'],
            'platform' => $stdTrade['platform'],
        ];
        $tradeItems = SysStdTradeItem::where($where)->get();

        $rootElement = $this->getRootElement($stdTrade, $tradeItems);
        $content = $this->getContent($stdTrade, $tradeItems);

        return ArrayToXml::convert($content, $rootElement, false, 'UTF-8');
    }

    protected function getRootElement($trade, $tradeItems)
    {
        $defaultReason = '预售订单未支付尾款取消订单';

        return [
            'rootElementName' => 'Order',
            '_attributes'     => [
                'Action'                 => 'MODIFY',
                'DocumentType'           => '0001',
                'EnterpriseCode'         => $trade['shop_code'],
                'OrderNo'                => $this->generatorOrderNo($trade['tid'], $trade['platform']),
                'CustomerPONo'           => (string)$trade['tid'],
                'ModificationReasonCode' => $this->tradeCancelReasonCodeMap($defaultReason, $trade['platform']),
            ],
        ];
    }

    protected function getContent($trade, $tradeItems)
    {
        $orderLines = [];
        if (!empty($tradeItems)) {
            $orderLines = $this->formatOrderLines($trade, $tradeItems->toArray());
        }

        $content = [
            'OrderLines' => [
                'OrderLine' => $orderLines,
            ],
        ];

        return $content;
    }

    protected function formatOrderLines($trade, $tradeItems)
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
                    'CustomerLinePONo' => empty($item['oid']) ? $item['row_index'] : $item['oid'],
                ],
                'Extn'        => [
                    '_attributes' => [
                        'ExtnRefundId'     => '',
                        'ExtnRefundAmount' => 0.00,
                    ],
                ],
            ];
            $orderLines[] = $orderLine;
        }

        return $orderLines;
    }
}