<?php


namespace App\Services\Hub\Adidas\Request\Transformer;



use App\Models\TaobaoInvoiceApply;

/**
 * 发票创建
 *
 * Class InvoiceCreateTransformer
 * @package App\Services\Hub\Adidas\Request
 */
class InvoiceCreateTransformer  extends BaseTransformer
{
    public function format($invoiceApply)
    {
        $content = $this->getContent($invoiceApply);

        return json_encode($content);
    }

    protected function getContent($invoiceApply)
    {
        $orders = data_get($invoiceApply, 'origin_detail.Order', []);
        $apply = $invoiceApply['origin_content'];

        // 判断是不是已经推送过，并且是修改抬头
        $updated = 0;
        if (isset($invoiceApply['trigger_status']) && 'invoice_change' == $invoiceApply['trigger_status']) {
            $isPushed = TaobaoInvoiceApply::where('push_status', 1)->first();
            if ($isPushed) {
                $updated = 1;
            }
        }

        return compact('apply', 'orders', 'updated');
    }
}
