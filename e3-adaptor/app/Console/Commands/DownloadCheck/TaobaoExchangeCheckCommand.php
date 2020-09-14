<?php


namespace App\Console\Commands\DownloadCheck;


use App\Jobs\DingTalkNoticeTextSendJob;
use App\Models\Sys\Shop;
use App\Models\TaobaoExchange;
use App\Services\Adaptor\Taobao\Api\Exchange;
use App\Services\Adaptor\Taobao\Jobs\BatchDownload\ExchangeBatchDownloadJob;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Log;

class TaobaoExchangeCheckCommand extends Command
{
    protected $signature = 'adaptor:taobao_check:exchange_download
                            {--shop_code= : The names of the shop to download}
                            {--status=2,3,4,14 : 换货待处理(1), 待买家退货(2), 买家已退货，待收货(3), 换货关闭(4), 换货成功(5), 待买家修改(6), 待发出换货商品(12), 待买家收货(13), 请退款(14)}
                            {--create_from= : 下载开始时间 yyy-mm-dd hh:ii:ss}
                            {--create_to= : 下载结束时间 yyy-mm-dd hh:ii:ss}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '淘宝换货单 检查待买家退货,家已退货，待收货,换货关闭 状态的未下载格式化的数据';

    public function handle()
    {
        if ('taobao' != config('adaptor.default')) {
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
            $to = Carbon::now()->subMinutes(10)->toDateTimeString(); // 10 个分钟前
        }

        $this->info('create_from' . $from);
        $this->info('create_to' . $to);
        if ($this->hasOption('status') && !empty($this->option('status'))) {
            $status = $this->option('status');
        } else {
            $status = '2,3,4,14';
        }

        $pageSize = 50;
        $jobBatch = 50;

        $this->info('start checking from service api:'. Carbon::now()->toDateTimeString());
        // 校验 换货单正常下载

        $shops = Shop::available('taobao')->get()->toArray();

        foreach ($shops as $shop) {
            $start = strtotime($from);
            $end = strtotime($to);

            $endTemp = 0;
            $timeRange = [];
            // 如果超过了1 天，则划分每1天下载一次
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
                        'status'      => $status,
                        'start_modified' => Carbon::createFromTimestamp($item['start'])->toDateTimeString(),
                        'end_modified'   => Carbon::createFromTimestamp($item['end'])->toDateTimeString(),
                    ];
                    $exchangeServer = new Exchange($shop);
                    do {
                        $where['page'] = $page;
                        $this->info(sprintf('%s:当前查询：%s, page:%s', $shop['code'], $where['start_modified'], $page));
                        // 查询列表
                        $exchanges = $exchangeServer->page($where);
                        if (empty($exchanges)) {
                            break;
                        }
                        $dispute_ids = [];
                        foreach ($exchanges as $exchange) {
                            $dispute_ids[] = $exchange['dispute_id'];
                        }
                        if (!empty($dispute_ids)) {
                            $existExchanges = TaobaoExchange::whereIn('dispute_id', $dispute_ids)->get(['dispute_id']);
                            if ($existExchanges->isNotEmpty()) {
                                $dispute_ids = array_diff($dispute_ids, $existExchanges->pluck('dispute_id')->toArray());
                            }
                            if (empty($dispute_ids)) {
                                $page++;
                                continue;
                            }
                        }
                        $params = ['dispute_ids' => $dispute_ids, 'shop_code' => $shop['code'], 'key' => $page];
                        dispatch(new ExchangeBatchDownloadJob($params));
                        // 通知部分订单，用于检查
                        $chunkNoticeTids = array_chunk($dispute_ids, 10);
                        $noticeTids = $chunkNoticeTids[0];
                        $message = sprintf("淘宝换货单漏单检查，检查到漏单数量：%s, 已经触发任务重试，请检查其中几个：%s",
                                           count($dispute_ids), implode(',', $noticeTids));
                        dispatch(new DingTalkNoticeTextSendJob(['message' => $message]));

                        $page++;
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
