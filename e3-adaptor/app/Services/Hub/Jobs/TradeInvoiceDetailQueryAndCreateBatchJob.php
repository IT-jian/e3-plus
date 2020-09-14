<?php


namespace App\Services\Hub\Jobs;


use App\Jobs\Job;
use App\Models\TaobaoInvoiceApply;
use App\Services\AisinoInvoiceServer;
use App\Services\Platform\Taobao\Client\Top\Exceptions\TaobaoTopServerSideException;
use Carbon\Carbon;
use Exception;

class TradeInvoiceDetailQueryAndCreateBatchJob extends Job
{
    // @todo 推送队列优先级
    public $queue = 'default';

    /**
     * @var array
     */
    public $applyIds;

    // 重试三次
    public $tries = 3;
    // 间隔十秒
    public $delay = 60;
    // maxTries

    /**
     * TaobaoInvoiceApply constructor.
     *
     * @param array $applyIds
     */
    public function __construct($applyIds)
    {
        $this->applyIds = $applyIds;
    }

    /**
     * @throws Exception
     *
     * @author linqihai
     * @since 2020/1/18 16:38
     */
    public function handle()
    {
        $applyIds = $this->applyIds;
        $invoiceApplies = TaobaoInvoiceApply::whereIn('apply_id', $applyIds)->where('push_status', 0)->get();
        if (empty($invoiceApplies)) {
            return true;
        }
        $failIds = [];
        foreach ($invoiceApplies as $invoiceApply) {
            try {
                $this->processInvoice($invoiceApply);
            } catch (Exception $e) {
                if (in_array($e->getMessage(), ['Order Fulfillment not yet completed', 'Order fully cancelled'])) {
                    continue;
                }
                $failIds[] = $invoiceApply['apply_id'];
            }
        }
        if ($failIds) {
            $this->release(300);
        }

        return true;
    }

    /**
     * @param TaobaoInvoiceApply $invoiceApply
     * @throws Exception
     */
    public function processInvoice(TaobaoInvoiceApply $invoiceApply)
    {
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
                throw new Exception($e->getMessage());
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
                $invoiceApply->error_msg = $response['message'];
                $invoiceApply->next_query_at = Carbon::now()->addHours(6);
                if ('Order fully cancelled' == $response['message']) {
                    $invoiceApply->push_status = 3; // 不再重试
                }
                $invoiceApply->save();
                throw new Exception($response['message'] ?? 'fetch detail fail');
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
