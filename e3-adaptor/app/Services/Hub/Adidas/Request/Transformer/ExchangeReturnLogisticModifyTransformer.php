<?php


namespace App\Services\Hub\Adidas\Request\Transformer;


use App\Models\SysStdTrade;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * 换货单买家更新退单物流单号
 *
 * Class ExchangeReturnLogisticModifyTransformer
 * @package App\Services\Hub\Adidas\Request\Transformer
 *
 * @todo 更新body
 *
 * @author linqihai
 * @since 2020/1/6 15:10
 */
class ExchangeReturnLogisticModifyTransformer extends BaseTransformer
{
    public function format($stdExchange)
    {
        $rootElement = $this->getRootElement($stdExchange);
        $content = $this->getContent($stdExchange);
        // 判断是否加强版报文
        if (cutoverTrade($stdExchange['tid'], $stdExchange['platform'])) { // 加强版报文
            $content['Extn'] = [
                '_attributes' => [
                    'ExtnIsMigratedOrder' => 'Y',
                    'ExtnStatus' => $stdExchange['status'],
                ],
            ];
        }

        return ArrayToXml::convert($content, $rootElement, false, 'UTF-8');
    }

    private function getRootElement($exchange)
    {
        return [
            'rootElementName' => 'Order',
            '_attributes' => [
                'EnterpriseCode' => $exchange['shop_code'],
                'DocumentType'   => '0001',
                'CustomerPONo'   => (string)$exchange['dispute_id'],
                'OrderNo'        => $this->generatorExchangeNo($exchange['dispute_id'], $exchange['platform']),
            ],
        ];
    }

    private function getContent($exchange)
    {
        $content = [];
        $content['OrderLines']['OrderLine'][] = [
            'Extn' => [
                '_attributes' => [
                    // 'ExtnStatus'     => $exchange['status'],
                    'ExtnRefundId'   => '',
                    'ExtnDisputeId'  => $exchange['dispute_id'],
                    'ExtnSCAC'       => empty($exchange['buyer_logistic_name']) ? 'SF' : $exchange['buyer_logistic_name'],
                    'ExtnTrackingNo' => $exchange['buyer_logistic_no'],
                ]
            ],
        ];

        return $content;
    }
}
