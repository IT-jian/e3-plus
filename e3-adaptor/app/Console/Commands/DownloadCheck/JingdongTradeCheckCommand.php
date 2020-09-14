<?php


namespace App\Console\Commands\DownloadCheck;


use App\Jobs\DingTalkNoticeTextSendJob;
use App\Models\Sys\Shop;
use App\Models\SysStdPushQueue;
use App\Models\SysStdTrade;
use App\Services\Adaptor\Jingdong\Events\JingdongTradeCreateEvent;
use App\Services\Adaptor\Jingdong\Jobs\JingdongTradeBatchTransferJob;
use App\Services\Adaptor\Jingdong\Repository\Rds\JingdongRdsTradeRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Log;

class JingdongTradeCheckCommand extends Command
{
    protected $signature = 'adaptor:jingdong_check:trade_download
                            {--status=WAIT_SELLER_STOCK_OUT : 1）WAIT_SELLER_STOCK_OUT 等待出库 2）WAIT_GOODS_RECEIVE_CONFIRM 等待确认收货 3）WAIT_SELLER_DELIVERY 等待发货（只适用于海外购商家，含义为“等待境内发货”标签下的订单,非海外购商家无需使用）}
                            {--create_from= : 下载开始时间 yyy-mm-dd hh:ii:ss}
                            {--create_to= : 下载结束时间 yyy-mm-dd hh:ii:ss}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '京东订单RDS 检查待发货订单未下载格式化的数据';

    public function handle()
    {
        if ('jingdong' != config('adaptor.default')) {
            return true;
        }
        if ($this->hasOption('create_from') && !empty($this->option('create_from'))) {
            $from = $this->option('create_from');
        } else {
            $from = Carbon::now()->subHours(2)->toDateTimeString(); // 最近 2 小时
        }

        if ($this->hasOption('create_to') && !empty($this->option('create_to'))) {
            $to = $this->option('create_to');
        } else {
            $to = Carbon::now()->subMinutes(10)->toDateTimeString(); // 10 分钟前
        }

        $this->info('create_from' . $from);
        $this->info('create_to' . $to);
        if ($this->hasOption('status') && !empty($this->option('status'))) {
            $status = $this->option('status');
        } else {
            $status = 'WAIT_SELLER_STOCK_OUT';
        }

        $where = [
            ['pushModified', '>=', $from],
            ['pushModified', '<', $to],
            ['state', $status],
        ];

        $pageSize = 5000;
        $jobBatch = 500;

        $sellerNicks = [];
        $shops = Shop::available('jingdong')->get();
        foreach ($shops as $shop) {
            $sellerNicks[] = $shop['seller_nick'];
        }
        $this->info('start checking from rds:'. Carbon::now()->toDateTimeString());
        // 校验 订单是否正常下载
        $rds = new JingdongRdsTradeRepository();
        $total = $rds->builder()->where($where)->whereIn('venderId', $sellerNicks)->count();
        Log::debug('pop jingdong rds trade total：' . $total, $where);
        if ($total) {
            $result = $rds->builder()->select(['orderId'])->where($where)->whereIn('venderId', $sellerNicks)
                ->orderBy('pushModified')->chunk($pageSize, function ($results, $page) use ($where, $jobBatch) {
                    $results->pluck('orderId')->chunk($jobBatch)->each(function ($orderIds, $k) use ($page) {
                        $existTrades = SysStdTrade::whereIn('tid', $orderIds)->get(['tid']);
                        if ($existTrades->isNotEmpty()) {
                            try {
                                $this->pushTradeCheck($existTrades->pluck('tid')->toArray());
                            } catch (\Exception $e) {
                                \Log::error('check push trade fail' . $e);
                            }
                            $orderIds = array_diff($orderIds->toArray(), $existTrades->pluck('tid')->toArray()); // 找出本地未存在的 tid
                        }
                        if ($orderIds) {
                            $key = 'jingdong_trade_check_page_' . $k;
                            dispatch((new \App\Services\Adaptor\Jingdong\Jobs\BatchDownload\TradeBatchDownloadJob(['order_ids' => $orderIds, 'platform' => 'jingdong', 'key' => $key]))->chain(
                                [
                                    new JingdongTradeBatchTransferJob(['order_ids' => $orderIds, 'key' => $key]),
                                ]));
                            // 通知部分订单，用于检查
                            $chunkNoticeTids = array_chunk($orderIds, 10);
                            $noticeTids = $chunkNoticeTids[0];
                            $message = sprintf("京东待发货订单漏单检查，检查到漏单数量：%s, 已经触发任务重试，请检查其中几个订单：%s",
                                               count($orderIds), implode(',', $noticeTids));
                            dispatch(new DingTalkNoticeTextSendJob(['message' => $message]));
                        }
                    });
                });
        }

        return true;
    }

    public function pushTradeCheck($tids)
    {
        $where = [];
        $where['method'] = 'tradeCreate';
        $where['platform'] = 'jingdong';
        $queues = SysStdPushQueue::where($where)->whereIn('bis_id', $tids)->get();
        // 找出本地未存在的 tid
        $tids = array_diff($tids, $queues->pluck('bis_id')->toArray());
        if ($tids) {
            $stdTrades = SysStdTrade::whereIn('tid', $tids)->get();
            if ($stdTrades->isNotEmpty()) {
                $chunkNoticeTids = array_chunk($tids, 10);
                $noticeTids = implode($chunkNoticeTids[0]);
                \Event::dispatch(new JingdongTradeCreateEvent($stdTrades));
                dispatch(new DingTalkNoticeTextSendJob(['message' => '京东待发货订单未及时推送检测到： ' . count($tids) . "，以重新处理:{$noticeTids}"]));
            }
        }
    }
}
