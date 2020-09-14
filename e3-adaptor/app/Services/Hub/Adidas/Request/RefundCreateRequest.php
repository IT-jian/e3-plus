<?php


namespace App\Services\Hub\Adidas\Request;


use App\Models\SysStdRefund;
use App\Services\Hub\Adidas\BaseRequest;
use App\Services\Hub\Adidas\Request\Transformer\RefundCreateTransformer;
use Illuminate\Contracts\Support\Arrayable;

/**
 * GWC 创建仅退款
 * 订单已经发货，消费者申请退运费等部分只退款不退货的退款请求，发货之后仅退款
 *
 * Class RefundCreateRequest
 * @package App\Services\Hub\Adidas\Request
 *
 * @author linqihai
 * @since 2020/1/6 15:54
 */
class RefundCreateRequest extends BaseRequest implements RequestContract
{
    protected $apiName = 'eai/baison/gwccreation';

    public $keyword = '';

    public function setContent($id)
    {
        if (is_array($id) || $id instanceof Arrayable) {
            $stdRefund = $id;
        } else {
            $stdRefund = SysStdRefund::where('refund_id', $id)->first();
        }
        $this->setDataVersion(strtotime($stdRefund['modified']));
        $this->keyword = $stdRefund['refund_id'] ?? '';
        $this->data = $this->getTransformer()->format($stdRefund);

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

        $stdRefund = SysStdRefund::where('refund_id', $id)->first();

        $this->setDataVersion(strtotime($stdRefund['modified']));
        $this->keyword = $id;
        if ($pushVersion && $pushVersion > 0 && $pushContent && ($pushVersion >= $this->dataVersion)) {
            $this->data = $pushContent;
        } else { // 调用 transformer
            $this->setContent($stdRefund);
        }

        return $this;
    }

    /**
     *
     * @return RefundCreateTransformer
     */
    public function getTransformer()
    {
        return app()->make(RefundCreateTransformer::class);
    }
}