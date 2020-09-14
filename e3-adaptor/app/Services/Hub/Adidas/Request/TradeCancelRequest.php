<?php


namespace App\Services\Hub\Adidas\Request;


use App\Facades\TopClient;
use App\Models\SysStdRefund;
use App\Services\Hub\Adidas\BaseRequest;
use App\Services\Hub\Adidas\Request\Transformer\TradeCancelTransformer;
use App\Services\Platform\Taobao\Client\Top\Request\RdcAligeniusSendgoodsCancelRequest;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;

/**
 * 订单取消 - 通过退单触发
 * 订单发货之前申请退款，并且退款成功之后下发
 *
 * Class TradeCancelRequest
 * @package App\Services\Hub\Adidas\Request
 *
 * @author linqihai
 * @since 2020/1/6 17:49
 */
class TradeCancelRequest extends BaseRequest implements RequestContract
{
    protected $apiName = 'eai/baison/inflightcancel';

    public $format = 'json';

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
     * @return TradeCancelTransformer
     */
    public function getTransformer()
    {
        return app()->make(TradeCancelTransformer::class);
    }
}
