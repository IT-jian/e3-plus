<?php


namespace App\Services\Hub\Adidas\Request\Transformer;


use App\Models\SysStdTrade;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * 退货退款买家更新退单物流单号
 *
 * Class RefundReturnLogisticModifyTransformer
 * @package App\Services\Hub\Adidas\Request\Transformer
 *
 * @author linqihai
 * @since 2020/1/6 15:10
 */
class RefundReturnLogisticModifyTransformer extends BaseTransformer
{
    public function format($stdRefund)
    {
        $rootElement = $this->getRootElement($stdRefund);
        $content = $this->getContent($stdRefund);

        // 判断是否加强版报文
        if (cutoverTrade($stdRefund['tid'], $stdRefund['platform'])) {
            $content['Extn']['_attributes']['ExtnIsMigratedOrder'] = 'Y';
        }

        return ArrayToXml::convert($content, $rootElement, false, 'UTF-8');
    }

    private function getRootElement($refund)
    {
        return [
            'rootElementName' => 'Order',
            '_attributes' => [
                'EnterpriseCode' => $refund['shop_code'],
                'DocumentType'   => '0003',
                'CustomerPONo'   => (string)$refund['refund_id'],
                'OrderNo'        => $this->generatorRefundNo($refund['refund_id'], $refund['platform']),
            ],
        ];
    }

    private function getContent($refund)
    {
        $content = [];
        $content['Extn'] = [
            '_attributes' => [
                'ExtnStatus' => $refund['status'],
            ],
        ];
        $content['OrderLines']['OrderLine'][] = [
            'Extn' => [
                '_attributes' => [
                    'ExtnRefundId'   => $refund['refund_id'],
                    'ExtnDisputeId'  => '',
                    'ExtnSCAC'       => empty($refund['company_name']) ? 'SF' : $refund['company_name'],
                    'ExtnTrackingNo' => $refund['sid'],
                ]
            ],
        ];

        return $content;
    }
}
