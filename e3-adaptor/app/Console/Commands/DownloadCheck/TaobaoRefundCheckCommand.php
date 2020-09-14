<?php


namespace App\Console\Commands\DownloadCheck;


use App\Jobs\DingTalkNoticeTextSendJob;
use App\Models\SysStdPushQueue;
use App\Models\SysStdRefund;
use App\Models\SysStdTrade;
use App\Services\Adaptor\Taobao\Events\TaobaoRefundCreateEvent;
use App\Services\Adaptor\Taobao\Events\TaobaoTradeCreateEvent;
use App\Services\Adaptor\Taobao\Jobs\BatchDownload\RefundBatchDownloadJob;
use App\Services\Adaptor\Taobao\Jobs\RefundBatchTransferJob;
use App\Services\Adaptor\Taobao\Repository\Rds\TaobaoRdsRefundRepository;
use App\Services\Adaptor\Taobao\Repository\Rds\TaobaoRdsTradeRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TaobaoRefundCheckCommand extends Command
{
    protected $signature = 'adaptor:taobao_check:refund_download
                            {--shop_code= : The names of the shop to download}
                            {--status= : WAIT_SELLER_AGREE(买家已经申请退款，等待卖家同意) WAIT_BUYER_RETURN_GOODS(卖家已经同意退款，等待买家退货) WAIT_SELLER_CONFIRM_GOODS(买家已经退货，等待卖家确认收货) SELLER_REFUSE_BUYER(卖家拒绝退款) CLOSED(退款关闭) SUCCESS(退款成功)}
                            {--create_from= : 下载开始时间 yyy-mm-dd hh:ii:ss}
                            {--create_to= : 下载结束时间 yyy-mm-dd hh:ii:ss}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '淘宝退单RDS 检查待发货订单未下载格式化的数据';

    public function handle()
    {
        if ('taobao' != config('adaptor.default')) {
            return true;
        }

        if ($this->hasOption('create_from') && !empty($this->option('create_from'))) {
            $from = $this->option('create_from');
        } else {
            $from = Carbon::now()->subHours(2)->toDateTimeString(); // 最近 1 小时
        }

        if ($this->hasOption('create_to') && !empty($this->option('create_to'))) {
            $to = $this->option('create_to');
        } else {
            $to = Carbon::now()->subMinutes(10)->toDateTimeString(); // 10 个分钟前
        }

        $this->info('create_from' . $from);
        $this->info('create_to' . $to);
        if ($this->hasOption('status') && !empty($this->option('status'))) {
            $statusArr = explode(',', $this->option('status'));
        } else {
            $statusArr = ['WAIT_SELLER_AGREE', 'WAIT_BUYER_RETURN_GOODS', 'WAIT_SELLER_CONFIRM_GOODS', 'SUCCESS'];
        }

        $where = [
            ['jdp_modified', '>=', $from],
            ['jdp_modified', '<', $to],
        ];

        $pageSize = 5000;
        $jobBatch = 500;

        $this->info('start checking from rds:'. Carbon::now()->toDateTimeString());
        // 校验 退单是否正常下载
        $rds = new TaobaoRdsRefundRepository();
        $rds->builder()->select(['refund_id', 'jdp_response', 'tid'])->where($where)->whereIn('status', $statusArr)
            ->orderBy('jdp_modified')->chunk($pageSize, function ($results, $page) use ($where, $jobBatch) {
                $results->chunk($jobBatch)->each(function ($rdsRefunds, $k) use ($page) {
                    $refundIds = $tids = [];
                    foreach ($rdsRefunds as $key => $rdsRefund) {
                        $tids[] = $rdsRefund->tid;
                    }
                    $taobaoTrades = (new TaobaoRdsTradeRepository)->builder()->whereIn('tid', $tids)->where('created', '>=', '2020-07-21 10:00:00')->get(['tid']);
                    if ($taobaoTrades->isEmpty()){
                        return true;
                    }
                    $validTids = $taobaoTrades->pluck('tid')->toArray();
                    foreach ($rdsRefunds as $key => $rdsRefund) {
                        if (!in_array($rdsRefund->tid, $validTids)) {
                            continue;
                        }
                        $originRefund = json_decode($rdsRefund->jdp_response, true);
                        $originRefund = data_get($originRefund, 'refund_get_response.refund', []);
                        if (in_array($originRefund['status'], ['WAIT_SELLER_AGREE', 'SUCCESS']) && 'false' == $originRefund['has_good_return']
                            && in_array($originRefund['order_status'], ['WAIT_SELLER_SEND_GOODS', 'ALL_CLOSED', 'TRADE_CLOSED', 'TRADE_CLOSED_BY_TAOBAO'])) {
                            $refundIds[] = $originRefund['refund_id'];
                        } else if (in_array($originRefund['status'], ['WAIT_BUYER_RETURN_GOODS', 'WAIT_SELLER_CONFIRM_GOODS'])) {
                            $refundIds[] = $originRefund['refund_id'];
                        }
                    }
                    if (empty($refundIds)) {
                        return true;
                    }
                    $noticeMessages = [];
                    $existTrades = SysStdRefund::whereIn('refund_id', $refundIds)->get(['refund_id']);
                    if ($existTrades->isNotEmpty()) {
                        try {
                            $this->pushRefundCheck($existTrades);
                        } catch (\Exception $e) {
                            \Log::error('check push refund fail' . $e);
                        }
                        $refundIds = array_diff($refundIds, $existTrades->pluck('refund_id')->toArray()); // 找出本地未存在的 refund_id
                        foreach ($rdsRefunds as $rdsRefund) {
                            if (in_array($rdsRefund->refund_id, $refundIds)) {
                                $originRefund = json_decode($rdsRefund->jdp_response, true);
                                $originRefund = data_get($originRefund, 'refund_get_response.refund', []);
                                $noticeMessages[] = sprintf("refund_id:%s,status:%s,order_status:%s|", $originRefund['refund_id'], $originRefund['status'], $originRefund['order_status']);
                            }
                        }
                    }
                    if ($refundIds) {
                        $key = 'taobao_refund_check_page_' . $k;
                        dispatch((new RefundBatchDownloadJob(['refund_ids' => $refundIds, 'platform' => 'taobao', 'key' => $key]))->chain(
                            [
                                new RefundBatchTransferJob(['refund_ids' => $refundIds, 'key' => $key]),
                            ]));
                        // 通知部分，用于检查
                        $chunkNoticeRefundIds = array_chunk($noticeMessages, 10);
                        $noticeRefunds = $chunkNoticeRefundIds[0];
                        $message = sprintf("淘宝退单漏单检查，检查到漏单数量：%s, 已经触发任务重试，请检查其中几个退单：%s",
                                           count($refundIds), implode(',', $noticeRefunds));
                        dispatch(new DingTalkNoticeTextSendJob(['message' => $message]));
                    }
                });
            });
        $this->info('checked'. Carbon::now()->toDateTimeString());
    }


    public function pushRefundCheck($refundIds)
    {
        $refunds = SysStdRefund::whereIn('refund_id', $refundIds)->get();
        $tradeCancels = $refundCreates = [];
        foreach ($refunds as $refund) {
            if (0 == $refund['has_good_return']) {
                // 退款单未及时推送
                $tradeCancels[$refund['refund_id']] = $refund;
            } else {
                // 退货单未及时推送
                $refundCreates[$refund['refund_id']] = $refund;
            }
        }
        if ($tradeCancels) {
            $where = [];
            $where['method'] = 'tradeCancel';
            $where['platform'] = 'taobao';
            $where['status'] = 1;
            $queues = SysStdPushQueue::where($where)->whereIn('bis_id', array_keys($tradeCancels))->get();
            $this->info('检测到取消推送队列：'. count($queues) . '检测RDS数：' .count($tradeCancels));
            // 找出本地未存在的 tid
            foreach ($queues as $queue) {
                unset($tradeCancels[$queue['bis_id']]);
            }
            if ($tradeCancels) {
                // 查询订单是否已经推送，如果未推送，则先推送订单
                // $this->pushTrade($tradeCancels);
                $tryPushRefunds = [];
                foreach ($tradeCancels as $refundId => $stdRefund) {
                    if (in_array($stdRefund['status'], ['WAIT_SELLER_AGREE', 'SUCCESS']) && 0 == $stdRefund['has_good_return']
                        && in_array($stdRefund['order_status'], ['WAIT_SELLER_SEND_GOODS', 'ALL_CLOSED', 'TRADE_CLOSED', 'TRADE_CLOSED_BY_TAOBAO'])) {
                        if (!cutoverTrade($stdRefund['tid'], $stdRefund['platform'])) {
                            $tryPushRefunds[$refundId] = $stdRefund;
                        }
                    }
                }
                if ($tryPushRefunds) {
                    $chunkNoticeTids = array_chunk(array_keys($tryPushRefunds), 10);
                    $noticeTids = implode(',', $chunkNoticeTids[0]);
                    \Event::dispatch(new TaobaoRefundCreateEvent($tryPushRefunds));
                    dispatch(new DingTalkNoticeTextSendJob(['message' => '淘宝退款单未及时推送检测到： ' . count($tryPushRefunds) . "，已经重新处理，例如：{$noticeTids}"]));
                }
            }
        }
        if ($refundCreates) {
            $where = [];
            $where['platform'] = 'taobao';
            $queues = SysStdPushQueue::where($where)->whereIn('method', ['refundReturnCreate', 'refundReturnCreateExtend'])->whereIn('bis_id', array_keys($refundCreates))->get();
            // 找出本地未存在的 tid
            foreach ($queues as $queue) {
                unset($refundCreates[$queue['bis_id']]);
            }
            if ($refundCreates) {
                $tryPushRefunds = [];
                foreach ($refundCreates as $refundId => $stdRefund) {
                    if (in_array($stdRefund['status'], ['WAIT_BUYER_RETURN_GOODS', 'WAIT_SELLER_CONFIRM_GOODS'])) {
                        $tryPushRefunds[$refundId] = $stdRefund;
                    }
                }
                if ($tryPushRefunds) {
                    $chunkNoticeTids = array_chunk(array_keys($tryPushRefunds), 10);
                    $noticeTids = implode(',', $chunkNoticeTids[0]);
                    \Event::dispatch(new TaobaoRefundCreateEvent($tryPushRefunds));
                    dispatch(new DingTalkNoticeTextSendJob(['message' => '淘宝退货单未及时推送检测到： ' . count($tryPushRefunds) . "，已经重新处理，例如：{$noticeTids}"]));
                }
            }
        }
    }

    public function pushTrade($refunds)
    {
        $tids = array_column($refunds, 'tid');
        $trades = SysStdTrade::whereIn('tid', $tids)->where('pay_time', '>=', '2020-07-21 10:00:00')->get();
        if ($trades->isNotEmpty()) {
            $tradeTids = $trades->pluck('tid')->toArray();
            $where = [];
            $where['method'] = 'tradeCreate';
            $where['platform'] = 'taobao';
            $queues = SysStdPushQueue::where($where)->whereIn('bis_id', $tradeTids)->get();
            $diffTids = array_diff($tids, $queues->pluck('bis_id')->toArray());
            if ($diffTids) {
                $pushTrades = SysStdTrade::whereIn('tid', $tids)->get();
                \Event::dispatch(new TaobaoTradeCreateEvent($pushTrades));
            }
        }
    }
}
