<?php


namespace App\Services\Hub\Adidas\Request\Transformer;


/**
 * 发票查询
 *
 * Class InvoiceQueryTransformer
 * @package App\Services\Hub\Adidas\Request
 */
class InvoiceQueryTransformer extends BaseTransformer
{
    public function format($stdTrade)
    {
        $params = [
           'DocumentType' => '',
           'EnterpriseCode' => '',
           'CustomerPONo' => $stdTrade['tid'],
        ];

        return json_encode($params);
    }
}
