<?php


namespace App\Services\Hub\Adidas\Request;


use App\Models\SysStdTrade;
use App\Models\TaobaoInvoiceApply;
use App\Services\Hub\Adidas\BaseRequest;
use App\Services\Hub\Adidas\Request\Transformer\InvoiceCreateTransformer;
use Illuminate\Contracts\Support\Arrayable;

/**
 * 发票创建
 *
 * Class InvoiceQueryRequest
 * @package App\Services\Hub\Adidas\Request
 */
class InvoiceCreateRequest extends BaseRequest implements RequestContract
{
    protected $apiName = 'aisino.sh.fpkj';

    public $format = 'json';

    public $keyword = '';

    public function setContent($id)
    {
        if (is_array($id) || $id instanceof Arrayable) {
            $invoiceApply = $id;
        } else {
            $invoiceApply = TaobaoInvoiceApply::where('apply_id', $id)->first();
        }
        $this->setDataVersion(strtotime($invoiceApply['updated_at']));
        $this->keyword = $invoiceApply['platform_tid'] ?? '';
        $this->data = $this->getTransformer()->format($invoiceApply);

        return $this;
    }

    /**
     * 推送 -- 已经格式化的内容
     *
     * @param $params
     * @return $this
     *
     * @author linqihai
     * @since 2020/05/25 14:40
     */
    public function setFormatContent($params)
    {
        $id = $params->bis_id;
        $pushVersion = $params->push_version ?? 0;
        $pushContent = $params->push_content ?? '';

        $SysStdTrade = SysStdTrade::where('apply_id', $id)->first();

        $this->setDataVersion(strtotime($SysStdTrade['updated_at']));
        $this->keyword = $id;
        if ($pushVersion && $pushVersion > 0 && $pushContent && ($pushVersion >= $this->dataVersion)) {
            $this->data = $pushContent;
        } else { // 调用 transformer
            $this->setContent($SysStdTrade);
        }

        return $this;
    }

    /**
     *
     * @return InvoiceCreateTransformer
     */
    public function getTransformer()
    {
        return app()->make(InvoiceCreateTransformer::class);
    }
}
