<?php


namespace App\Services\Hub\Adidas\Request\Transformer;


/**
 * 发票创建
 *
 * Class AisinoInvoiceCreateTransformer
 * @package App\Services\Hub\Adidas\Request
 */
class AisinoInvoiceCreateTransformer  extends BaseTransformer
{
    public function format($invoiceApply)
    {
        $content = $this->getContent($invoiceApply);

        return json_encode($content);
    }

    protected function getContent($invoiceApply)
    {
        // omini 提供的详情
        $originDetail = json_decode($invoiceApply['origin_detail']);

        return data_get($originDetail, 'Order.0', []);
    }
}
