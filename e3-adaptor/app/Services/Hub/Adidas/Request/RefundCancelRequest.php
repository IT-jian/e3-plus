<?php


namespace App\Services\Hub\Adidas\Request;


use App\Models\SysStdRefund;
use App\Services\Hub\Adidas\BaseRequest;
use App\Services\Hub\Adidas\Request\Transformer\RefundCancelTransformer;
use Illuminate\Contracts\Support\Arrayable;

/**
 * GWC 创建仅退款
 *
 * Class RefundCreateRequest
 * @package App\Services\Hub\Adidas\Request
 *
 * @author linqihai
 * @since 2020/1/6 15:54
 */
class RefundCancelRequest extends BaseRequest implements RequestContract
{
    protected $apiName = 'eai/cancel/order';

    public $format = 'json';

    public $keyword = '';

    public $content = [];

    public function getApiMethodName()
    {
        return $this->apiName;
    }

    public function setContent($id)
    {
        if (is_array($id) || $id instanceof Arrayable) {
            $refundTrade = $id;
        } else {
            $refundTrade = SysStdRefund::where('refund_id', $id)->first();
        }
        $this->setDataVersion(strtotime($refundTrade['modified']));
        $this->keyword = $refundTrade['refund_id'] ?? '';
        $this->data = $this->getTransformer()->format($refundTrade);

        return $this;
    }


    /**
     * 按照推送队列处理
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

        $stdRefund = SysStdRefund::where('refund_id', $id)->first();

        $this->setDataVersion(strtotime($stdRefund['modified']));
        $this->keyword = $stdRefund['refund_id'] ?? '';
        if ($pushVersion && $pushVersion > 0 && $pushContent && ($pushVersion >= $this->dataVersion)) {
            $this->data = $pushContent;
        } else { // 调用 transformer
            $this->setContent($stdRefund);
        }

        return $this;
    }

    public function getBody()
    {
        return $this->data;
    }

    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     *
     * @return RefundCancelTransformer
     */
    public function getTransformer()
    {
        return app()->make(RefundCancelTransformer::class);
    }
}