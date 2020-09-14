<?php


namespace App\Console\Commands\DownloadCheck;


use App\Jobs\DingTalkNoticeTextSendJob;
use App\Models\JingdongRefund;
use App\Models\Sys\Shop;
use App\Services\Adaptor\Jingdong\Api\RefundUpdate;
use App\Services\Adaptor\Jingdong\Jobs\RefundDownloadJob;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Log;

class JingdongServiceCheckCommand extends Command
{
    protected $signature = 'adaptor:jingdong_check:service_download
                            {--shop_code= : The names of the shop to download}
                            {--status=10005 : 10005 待收货}
                            {--create_from= : 下载开始时间 yyy-mm-dd hh:ii:ss}
                            {--create_to= : 下载结束时间 yyy-mm-dd hh:ii:ss}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '京东服务单 检查待收货服务单未下载格式化的数据';

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

        $this->info('start checking from service api:'. Carbon::now()->toDateTimeString());
        // 校验 换货单正常下载

        $shops = Shop::available('jingdong')->get()->toArray();

        $retryServiceCount = 0;
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
                    $refundServer = new RefundUpdate($shop);
                    do {
                        $where['page'] = $page;
                        $this->info(sprintf('%s:当前查询：%s, page:%s', $shop['code'], $where['start_modified'], $page));
                        // 查询列表
                        $refundTrades = $refundServer->page($where);
                        if (empty($refundTrades)) {
                            break;
                        }
                        $serviceIds = [];
                        foreach ($refundTrades as $refundTrade) {
                            $serviceIds[] = $refundTrade['serviceId'];
                        }
                        if (!empty($serviceIds)) {
                            $existServices = JingdongRefund::whereIn('service_id', $serviceIds)->get(['service_id']);
                            if ($existServices->isNotEmpty()) {
                                $serviceIds = array_diff($serviceIds, $existServices->pluck('service_id')->toArray());
                            }
                            if (empty($serviceIds)) {
                                $page++;
                                continue;
                            }
                        }
                        foreach ((array)$refundTrades as $refundTrade) {
                            if (in_array($refundTrade['serviceId'], $serviceIds)) {
                                $refundTrade['shop'] = $shop;
                                dispatch((new RefundDownloadJob($refundTrade))->delay(rand(10, 30)));
                                $retryServiceCount++;
                            }
                        }
                        // 通知部分订单，用于检查
                        $chunkNoticeTids = array_chunk($serviceIds, 10);
                        $noticeTids = $chunkNoticeTids[0];
                        $message = sprintf("京东待收货服务单漏单检查，检查到漏单数量：%s, 已经触发任务重试，请检查其中几个：%s",
                                           count($serviceIds), implode(',', $noticeTids));
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
