<?php


namespace App\Services\Adaptor\Taobao\Jobs;


use App\Models\SysStdRefund;
use App\Models\SysStdTrade;
use App\Models\TaobaoTrade;
use App\Notifications\DingtalkNotification;
use App\Services\Adaptor\Taobao\Jobs\BatchDownload\TradeBatchDownloadJob;
use Calchen\LaravelDingtalkRobot\Message\TextMessage;
use Calchen\LaravelDingtalkRobot\Robot;
use Illuminate\Support\Facades\Notification;

/**
 * 退单转入时，订单还未转入，延迟转入
 *
 * 1. 下载订单
 * 2. 订单转入
 * 3. 退单转入
 *
 * 失败之后会钉钉通知
 *
 * Class RefundDelayTransferJob
 * @package App\Services\Adaptor\Taobao\Jobs
 *
 * @author linqihai
 * @since 2020/3/24 14:58
 */
class RefundDelayTransferJob extends BaseTransferJob
{
    public $delay = 300; // 5 分钟

    public $tries = 3;

    private $params;

    /**
     * RefundTransferJob constructor.
     *
     * @param $params
     */
    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @throws \Exception
     *
     * @author linqihai
     * @since 2020/1/18 16:38
     */
    public function handle()
    {
        $targetTrades = array_unique(array_values($this->params['delay_trades']));
        $targetRefunds = array_unique(array_keys($this->params['delay_trades']));
        // 不走队列，直接执行
        dispatch_now((new TradeBatchDownloadJob(['tids' => $targetTrades, 'platform' => 'taobao', 'key' => 'refund_delay']))
                         ->chain([
                                     new TaobaoTradeBatchTransferJob(['tids' => $targetTrades, 'key' => 'refund_transfer_after_trade']),
                                     new RefundBatchTransferJob(['refund_ids' => $targetRefunds, 'key' => 'refund_transfer_after_trade', 'from' => 'delay']) // 增加标识，防止一直循环
                                 ]));

        return true;
        // 校验订单是否全部下载
        $existTrades = TaobaoTrade::whereIn('tid', $targetTrades)->get(['tid']);
        if ($existTrades->count() < count($targetTrades)) {
            $diffTids = array_diff($targetTrades, $existTrades->pluck('tid')->toArray());
            // 订单下载
            dispatch_now(new TradeBatchDownloadJob(['tids' => $diffTids, 'platform' => 'taobao', 'key' => 'refund_transfer_after_trade']));
        }
        // 存在未转入的订单，执行转入
        $existTrades = TaobaoTrade::whereIn('tid', $targetTrades)->where('sync_status', 0)->get(['tid']);
        if ($existTrades->isNotEmpty()) {
            // 订单转入
            dispatch_now(new TaobaoTradeBatchTransferJob(['tids' => $existTrades->pluck('tid')->toArray(), 'key' => 'refund_transfer_after_trade']));
        } else {
            throw new \Exception('退单延迟转入失败，RDS 查询订单数据不存在');
        }
        $sysStdTradeCount = SysStdTrade::whereIn('tid', $targetTrades)->platform('taobao')->count();
        if ($sysStdTradeCount >= count($targetTrades)) { // 如果全部转入订单了，则触发退单转入
            // 退单转入
            dispatch(new RefundBatchTransferJob(['refund_ids' => $targetRefunds, 'key' => 'refund_transfer_after_trade']));
        } else { // 退单尝试转入
            dispatch_now(new RefundBatchTransferJob(['refund_ids' => $targetRefunds, 'key' => 'refund_transfer_after_trade']));
            // 查询退单是否已经全部转入
            $sysStdRefundCount = SysStdRefund::whereIn('refund_id', $targetRefunds)->platform('taobao')->count();
            if ($sysStdRefundCount < count($targetRefunds)) { // job 失败
                throw new \Exception('退单延迟转入失败，未成功转入退单数量：' . (count($targetRefunds) - $sysStdRefundCount));
            }
        }
    }

    public function failed($e)
    {
        // 告警通知
        $message = '退单因订单未转入，延迟转入失败：' . $e->getMessage();
        $msg = new TextMessage($message);

        Notification::send(new Robot(), new DingtalkNotification($msg));
    }

    /**
     * 获取分配给这个任务的标签
     *
     * @return array
     */
    public function tags()
    {
        return ['taobao_transfer', 'refund_transfer_delay'];
    }
}