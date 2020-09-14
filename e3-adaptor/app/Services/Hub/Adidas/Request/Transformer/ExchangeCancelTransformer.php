<?php


namespace App\Services\Hub\Adidas\Request\Transformer;


use App\Models\SysStdExchangeItem;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * 换货申请消费者主动取消或者超时关闭的，需要下发
 *
 * Class RefundReturnCancelTransformer
 * @package App\Services\Hub\Adidas\Request\Transformer
 *
 * @author linqihai
 * @since 2020/1/6 15:10
 */
class ExchangeCancelTransformer extends BaseTransformer
{
    public function format($stdExchange)
    {
        $rootElement = $this->getRootElement($stdExchange);
        $content = $this->getContent($stdExchange);

        return ArrayToXml::convert($content, $rootElement, false, 'UTF-8');
    }

    private function getRootElement($exchange)
    {
        return [
            'rootElementName' => 'Order',
            '_attributes'     => [
                'Action'                 => 'CANCEL',
                'Override'               => 'Y',
                'CustomerPONo'           => (string)$exchange['dispute_id'],
                'EnterpriseCode'         => $exchange['shop_code'],
                'OrderNo'                => $this->generatorExchangeNo($exchange['dispute_id'], $exchange['platform']),
                'SellerOrganizationCode' => $exchange['shop_code'],
                'DocumentType'           => '0001',
                'OrderPurpose'           => 'EXCHANGE',
                'ModificationReasonCode' => 'A11',
            ],
        ];
    }

    private function getContent($exchange)
    {
        $where = [
            'dispute_id' => $exchange['dispute_id'],
            'platform'   => $exchange['platform'],
        ];
        $orderLines = [];
        // 查询明细
        $exchangeItems = SysStdExchangeItem::where($where)->get();
        if (!empty($exchangeItems)) {
            $orderLines = $this->formatOrderLines($exchange, $exchangeItems->toArray());
        }
        // EXCHANGE_TRANSFER_REFUND
        $content = [
            'OrderLines' => [
                'OrderLine' => $orderLines,
            ],
        ];

        if ('EXCHANGE_TRANSFER_REFUND' == $exchange['status']) {
            $content['Extn'] = [
                '_attributes' => [
                    'ExtnStatus' => 'EXCHANGE_TRANSFER_REFUND',
                ],
            ];
        }
        return $content;
    }

    /**
     * 换货明细
     *
     * @param $exchange
     * @param $exchangeItems
     * @return array
     *
     * @author linqihai
     * @since 2020/2/17 17:19
     */
    private function formatOrderLines($exchange, $exchangeItems)
    {
        $orderLines = [];
        if (empty($exchangeItems)) {
            return $orderLines;
        }
        $extendCancel = false;
        if (cutoverTrade($exchange['tid'], $exchange['platform'])) {
            $extendCancel = true;
        }
        $defaultRowIndex = 0;
        foreach ($exchangeItems as $item) {
            if ($extendCancel) {
                $defaultRowIndex++;
                $item['row_index'] = $defaultRowIndex;
            }
            $orderLine = [
                '_attributes' => [
                    'PrimeLineNo'      => !empty($item['row_index']) ? $item['row_index'] : 1,
                    'QuantityToCancel' => !empty($item['num']) ? $item['num'] : 1,
                    'Action'           => 'CANCEL',
                ],
                'Extn'        => [
                    '_attributes' => [
                        'ExtnDisputeId' => $exchange['dispute_id'],
                    ],
                ],
            ];
            $orderLines[] = $orderLine;
        }

        return $orderLines;
    }
}
