<?php


namespace App\Console\Commands;


use App\Models\SysStdPushQueue;
use App\Models\SysStdTrade;
use App\Services\Adaptor\Jobs\PushQueueCreateJob;
use App\Services\Hub\PushQueueFormatType;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PushQueueInitCommand extends Command
{
    protected $name = 'adaptor:push_queue_init_order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化将标准订单';

    public function handle()
    {
        $limit = 500;
        $count = 0;
        do {
            $where = [];
            $trades = SysStdTrade::where($where)->limit($limit)->get(['tid']);
            if ($trades->isNotEmpty()) {
                $queues = [];
                foreach ($trades as $trade) {
                    $queues[] = [
                        'bis_id'     => $trade['tid'],
                        'platform'   => 'taobao',
                        'hub'        => 'adidas',
                        'method'     => 'tradeCreate',
                        'status'     => 0,
                        'extends'    => json_encode([]),
                        'created_at' => Carbon::now()->toDateTimeString(),
                        'push_content' => '',
                        'push_version' => 0,
                    ];
                }
                Log::error('queues', $queues);
                foreach (array_chunk($queues, 50) as $chunk) {
                    if (PushQueueFormatType::is(PushQueueFormatType::WHEN_PUSH_TO_QUEUE)) {
                        dispatch_now(new PushQueueCreateJob($chunk));
                    } else {
                        SysStdPushQueue::insert($chunk);
                    }
                }
            }
            $count += count($trades);
            if (count($trades) < $limit) {
                break;
            }
        } while (true);
        $this->info('处理完成，已经推送到队列');
    }
}
