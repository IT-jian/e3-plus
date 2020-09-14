<?php


namespace App\Services\Hub\Adidas\Request;


use App\Models\SysStdRefund;
use App\Services\Hub\Adidas\BaseRequest;
use App\Services\Hub\Adidas\Request\Transformer\RefundReturnCreateTransformer;
use Illuminate\Contracts\Support\Arrayable;

/**
 * 创建退货退款单
 * 订单发货之后，申请退货退款，并且卖家已同意退货请求
 *
 * Class RefundReturnCreateRequest
 * @package App\Services\Hub\Adidas\Request
 *
 * @author linqihai
 * @since 2020/1/6 15:55
 */
class RefundReturnCreateRequest extends BaseRequest implements RequestContract
{
    protected $apiName = 'eai/baison/returnorderexport';

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
     * @return RefundReturnCreateTransformer
     */
    public function getTransformer()
    {
        return app()->make(RefundReturnCreateTransformer::class);
    }
}