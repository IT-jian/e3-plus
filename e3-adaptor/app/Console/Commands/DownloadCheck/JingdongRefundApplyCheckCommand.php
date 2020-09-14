<?php


namespace App\Console\Commands\DownloadCheck;


use App\Jobs\DingTalkNoticeTextSendJob;
use App\Models\JingdongRefundApply;
use App\Models\Sys\Shop;
use App\Services\Adaptor\Jingdong\Api\RefundApplyQuery;
use App\Services\Adaptor\Jingdong\Jobs\RefundApplyDownloadJob;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Log;

class JingdongRefundApplyCheckCommand extends Command
{
    protected $signature = 'adaptor:jingdong_check:refund_apply_download
                            {--shop_code= : The names of the shop to download}
                            {--create_from= : 下载开始时间 yyy-mm-dd hh:ii:ss}
                            {--create_to= : 下载结束时间 yyy-mm-dd hh:ii:ss}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '京东退款单申请下载漏单检查';

    public function handle()
    {
        if ('jingdong' != config('adaptor.default')) {
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
            $status = $this->option('status');
        } else {
            $status = '10005';
        }

        $pageSize = 50;
        $jobBatch = 50;

        $this->info('start checking from reufnd api:'. Carbon::now()->toDateTimeString());
        // 校验 换货单正常下载

        $shops = Shop::available('jingdong')->get()->toArray();

        foreach ($shops as $shop) {
            $start = strtotime($from);
            $end = strtotime($to);

            $endTemp = 0;
            $timeRange = [];
            // 如果超过了 1 天，则划分每1天下载一次
            do {
                $endTemp = $start + 86400;
                if ($endTemp > $end) {
                    $endTemp = $end+1;
                }
                $timeRange[] = [
                    'start' => $start,
                    'end' => $endTemp,
                ];
                $start = $endTemp;
            } while ($end > $start);
            try {
                foreach ($timeRange as $item) {
                    $page = 1;
                    $where = [
                        'page'           => 1,
                        'page_size'      => $pageSize,
                        'start_modified' => Carbon::createFromTimestamp($item['start'])->toDateTimeString(),
                        'end_modified'   => Carbon::createFromTimestamp($item['end'])->toDateTimeString(),
                    ];
                    $refundServer = new RefundApplyQuery($shop);
                    do {
                        $where['page'] = $page;
                        $this->info(sprintf('%s:当前查询：%s, page:%s', $shop['code'], $where['start_modified'], $page));
                        // 查询列表
                        $refundApplies = $refundServer->page($where);
                        if (empty($refundApplies)) {
                            break;
                        }
                        $applyIds = [];
                        foreach ((array)$refundApplies as $key => $refundApply) {
                            $refundApplies[$key]['vender_id'] = $shop['seller_nick'];
                            $applyIds[] = $refundApply['id'];
                        }

                        if (!empty($applyIds)) {
                            $existApply = JingdongRefundApply::whereIn('id', $applyIds)->get(['id']);
                            if ($existApply->isNotEmpty()) {
                                $applyIds = array_diff($applyIds, $existApply->pluck('id')->toArray());
                            }
                            if (empty($applyIds)) {
                                if (99 <= $page) { // 该接口仅允许查询100页，重新设置查询时间
                                    $lastRefund = array_pop($refundApplies);
                                    $page = 1;
                                    $where['page'] = $page;
                                    $where['start_modified'] = $lastRefund['applyTime'];
                                } else {
                                    $page++;
                                }
                                continue;
                            }
                        }

                        $retryApplies = [];
                        foreach ((array)$refundApplies as $refundApply) {
                            if (in_array($refundApply['id'], $applyIds)) {
                                $retryApplies[] = $refundApply;
                            }
                        }
                        dispatch(new RefundApplyDownloadJob($retryApplies));

                        // 通知部分订单，用于检查
                        $chunkNoticeTids = array_chunk($applyIds, 10);
                        $noticeTids = $chunkNoticeTids[0];
                        $message = sprintf("京东退款申请漏单检查，检查到漏单数量：%s, 已经触发任务重试，请检查其中几个：%s",
                                           count($applyIds), implode(',', $noticeTids));
                        dispatch(new DingTalkNoticeTextSendJob(['message' => $message]));

                        if (99 <= $page) { // 该接口仅允许查询100页，重新设置查询时间
                            $lastRefund = array_pop($refundApplies);
                            $page = 1;
                            $where['page'] = $page;
                            $where['start_modified'] = $lastRefund['applyTime'];
                        } else {
                            $page++;
                        }
                    } while (true);
                }
            } catch (\Exception $e) {
                Log::debug(__CLASS__ . $e->getMessage());
            }
        }

        $this->info('checked'. Carbon::now()->toDateTimeString());

        return true;
    }
}
