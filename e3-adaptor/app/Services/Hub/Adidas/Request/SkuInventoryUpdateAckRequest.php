<?php


namespace App\Services\Hub\Adidas\Request;


use App\Models\SkuInventoryPlatformLog;
use App\Services\Hub\Adidas\BaseRequest;
use App\Services\Hub\Adidas\Request\Transformer\SkuInventoryUpdateAckTransformer;
use Illuminate\Contracts\Support\Arrayable;

/**
 * 天猫库存同步结果异步通知
 *
 * Class SkuInventoryUpdateAckRequest
 * @package App\Services\Hub\Adidas\Request
 *
 * @author linqihai
 * @since 2020/08/23 21:54
 */
class SkuInventoryUpdateAckRequest extends BaseRequest implements RequestContract
{
    protected $apiName = 'eai/baison/rtam/tmallreprocessasync';

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
            $log = $id;
        } else {
            $log = SkuInventoryPlatformLog::where('id', $id)->first();
        }
        $this->setDataVersion(strtotime($log['start_at']));
        $this->keyword = $log['id'] ?? '';
        $this->data = $this->getTransformer()->format($log);

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

        $log = SkuInventoryPlatformLog::where('id', $id)->first();

        $this->setDataVersion(strtotime($log['start_at']));
        $this->keyword = $log['id'] ?? '';
        if ($pushVersion && $pushVersion > 0 && $pushContent && ($pushVersion >= $this->dataVersion)) {
            $this->data = $pushContent;
        } else { // 调用 transformer
            $this->setContent($log);
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
     * @return SkuInventoryUpdateAckTransformer
     */
    public function getTransformer()
    {
        return app()->make(SkuInventoryUpdateAckTransformer::class);
    }
}
