<?php


namespace App\Services\Hub\Adidas\Request;


use App\Models\SysStdTrade;
use App\Services\Hub\Adidas\BaseRequest;
use App\Services\Hub\Adidas\Request\Transformer\InvoiceQueryTransformer;
use Illuminate\Contracts\Support\Arrayable;

/**
 * 发票查询
 *
 * Class InvoiceQueryRequest
 * @package App\Services\Hub\Adidas\Request
 */
class InvoiceQueryRequest extends BaseRequest implements RequestContract
{
    protected $apiName = 'eai/aisino/invoice';

    public $format = 'json';

    public $keyword = '';

    public function setContent($id)
    {
        if (is_array($id) || $id instanceof Arrayable) {
            $stdTrade = $id;
        } else {
            $stdTrade = SysStdTrade::where('tid', $id)->first();
        }
        $this->setDataVersion(strtotime($stdTrade['modified']));
        $this->keyword = $stdTrade['tid'] ?? '';
        $this->data = $this->getTransformer()->format($stdTrade);

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

        $SysStdTrade = SysStdTrade::where('tid', $id)->first();

        $this->setDataVersion(strtotime($SysStdTrade['modified']));
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
     * @return InvoiceQueryTransformer
     */
    public function getTransformer()
    {
        return app()->make(InvoiceQueryTransformer::class);
    }
}
