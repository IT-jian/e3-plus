<?php


namespace App\Console\Commands;


use App\Jobs\TaobaoTradeDownJob;
use App\Models\GetTaobaoTrade;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class GetTaobaoTradeListCommand extends Command
{
    protected $name = 'oms:get_trade_taobao';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '定时从 get_taobao_trade 表中读取待下载订单，推送到下载队列';

    public function handle()
    {
        $limit = 500;
        $count = 0;
        do {
            $tradeList = GetTaobaoTrade::where('sync_status', 0)->limit($limit)->lockForUpdate()->get(['tid', 'rds', 'status', 'sync_status', 'jdp_modified']);
            if ($tradeList) {
                $tids_str = $tradeList->implode('tid', "','");
                DB::update("UPDATE get_taobao_trade SET sync_status=9 WHERE tid IN ('{$tids_str}')");
                foreach ($tradeList as $trade) {
                    try {
                        dispatch(new TaobaoTradeDownJob($trade));
                    } catch (\Exception $e) {
                        Log::debug('---处理失败---');
                        continue;
                    }
                    // Queue::push(new TaobaoTradeDownJob($trade));
                }
            }
            $count += count($tradeList);
            if (count($tradeList) < $limit) {
                break;
            }
        } while (true);
        Log::debug("从 rds 表读取 get_taobao_trade 数据：{$count} 条");
        $this->info('GetTaobaoTradeListCommand 处理完成，已经推送到队列');
    }
}