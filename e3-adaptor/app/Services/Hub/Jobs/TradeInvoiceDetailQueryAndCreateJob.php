<?php


namespace App\Services\Hub\Jobs;


use App\Jobs\Job;
use App\Models\TaobaoInvoiceApply;
use App\Services\AisinoInvoiceServer;
use App\Services\Platform\Taobao\Client\Top\Exceptions\TaobaoTopServerSideException;
use Carbon\Carbon;
use Exception;

class TradeInvoiceDetailQueryAndCreateJob extends Job
{
    // @todo 推送队列优先级
    public $queue = 'default';

    /**
     * @var string
     */
    public $applyId;

    // 重试三次
    // public $tries = 3;
    // 间隔十秒
    // public $delay = 5;
    // maxTries

    /**
     * TaobaoInvoiceApply constructor.
     *
     * @param string $applyId
     */
    public function __construct($applyId)
    {
        $this->applyId = $applyId;
    }

    /**
     * @throws Exception
     *
     * @author linqihai
     * @since 2020/1/18 16:38
     */
    public function handle()
    {
        $applyId = $this->applyId;
        $invoiceApply = TaobaoInvoiceApply::where('apply_id', $applyId)->firstOrFail();
        $server = new AisinoInvoiceServer();

        if (empty($invoiceApply->origin_content)) { // 查询 阿里发票详情
            try {
                $detail = $server->fetchApply($invoiceApply);
            } catch (\Exception $e) {
                if ($e instanceof TaobaoTopServerSideException) {
                    if ('isv.apply-not-exists' == $e->getSubErrorCode()) {
                        $invoiceApply->query_at = Carbon::now();
                        $invoiceApply->push_status = 3; // 申请不存在，不再处理
                        $invoiceApply->error_msg = $e->getMessage(); // 错误信息
                        $invoiceApply->save();
                    }
                }
            }
            if (empty($detail)) {
                $invoiceApply->next_query_at = Carbon::now()->addHours(6);
                $invoiceApply->query_at = Carbon::now();
                $invoiceApply->save();
                throw new Exception('fetch detail fail');
            } else {
                $invoiceApply->origin_content = $detail;
                $invoiceApply->query_at = Carbon::now();
                $invoiceApply->query_status = 1;
                $invoiceApply->save();
            }
        }

        if (empty($invoiceApply->origin_detail)) { // 查询 aisino 发票详情
            $response = $server->fetchDetail($invoiceApply);
            if ($response['status']) {
                $invoiceApply->origin_detail = $response['data'];
                $invoiceApply->save();
            } else {
                if ('Order fully cancelled' == $response['message']) {
                    $invoiceApply->push_status = 3;
                }
                $invoiceApply->error_msg = $response['message']; // 错误信息
                $invoiceApply->save();
                if ('Order Fulfillment not yet completed' == $response['message']) {
                    // return true;
                }
                throw new Exception('fetch detail fail' . $response['message']);
            }
        }
        // 请求创建
        $response = $server->invoiceCreate($invoiceApply);
        if ($response['status']) {
            $invoiceApply->push_status = 1;
            $invoiceApply->pushed_at = Carbon::now();
            $invoiceApply->save();
        } else {
            throw new Exception('push fail' . $response['message']);
        }

        return true;
    }

    /**
     * 处理失败
     *
     * @param Exception $e
     *
     * @author linqihai
     * @since 2020/3/2 20:28
     */
    public function failed(Exception $e)
    {
        // 新增或者设置下一次查询时间
    }

    /**
     * 获取分配给这个任务的标签
     *
     * @return array
     */
    public function tags()
    {
        return ['invoice_apply_queue'];
    }
}
