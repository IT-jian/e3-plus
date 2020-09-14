<?php


namespace App\Console\Commands;


use App\Facades\HubApi;
use App\Models\SysStdRefund;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RetryRefundReturnHubApiRequestCommand extends Command
{
    protected $signature = 'adaptor:hub_api_request_retry:refund_return
                            {--delay_retry_minutes=30 : 失败之后，下一次尝试回写间隔（分钟）}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重试请求次数';

    public function handle()
    {
        if ($this->hasOption('delay_retry_minutes') && !empty($this->option('delay_retry_minutes'))) {
            $delayMinutes = $this->option('delay_retry_minutes');
        } else {
            $delayMinutes = 30;
        }

        $retryAt = Carbon::now()->timestamp;
        $count = $this->model()->where('method', 'refundReturnGoodsAgreeExtend')->whereIn('status', [0, 3])->where('retry_at', '<=', $retryAt)->count();
        $this->info('found record ' . $count);
        if (empty($count)) {

            return true;
        }

        $this->model()->where('method', 'refundReturnGoodsAgreeExtend')->whereIn('status', [0, 3])->where('retry_at', '<=', $retryAt)->chunkById(500, function ($requests) use ($delayMinutes) {
            $refundIds = $requests->pluck('bis_id')->toArray();
            $refunds = SysStdRefund::whereIn('refund_id', $refundIds)->get()->keyBy('refund_id');
            $successIds = [];
            foreach ($requests as $request) {
                $request = collect($request);
                $refund = $refunds[$request['bis_id']] ?? [];
                if (empty($refund)) {
                    $successIds[] = $request['id'];
                    continue;
                }
                if (!in_array($refund['status'], ['WAIT_SELLER_AGREE', 'WAIT_BUYER_RETURN_GOODS', 'WAIT_SELLER_CONFIRM_GOODS'])) {
                    $successIds[] = $request['id'];
                    continue;
                }
                $customer = $request['customer'] ?? 'adidas';
                $platform = $request['platform'] ?? 'taobao';
                $method = $request['method'];
                $content = json_decode($request['content'], true);
                try {
                    if (1 == $refund['has_good_return'] && empty($refund['sid'])) {
                        throw new \Exception('未查询到退货物流单号，不进行重试');
                    }

                    $response = HubApi::hub($customer)->platform($platform)->execute(compact('method', 'content'));
                    if (!$response['status']) {
                        throw new \Exception($response['message']);
                    }
                    $successIds[] = $request['id'];
                } catch (\Exception $e) {
                    $retryTimes = $request['retry_times']+1;
                    $update = [
                        'retry_at' => Carbon::now()->addMinutes($delayMinutes)->timestamp,
                        'status' => 3,
                        'retry_times' => $retryTimes,
                        'message' => $e->getMessage(),
                    ];
                    $this->model()->where('id', $request['id'])->update($update);
                }
            }
            if ($successIds) {
                $this->model()->whereIn('id', $successIds)->update(['status' => 1]);
            }
        });


        return true;

    }

    public function methodRetryAfterMinutes()
    {
        return [
            'refundReturnGoodsAgreeExtend' => 120,
        ];
    }

    public function model()
    {
        return \DB::table('retry_hub_api_request');
    }
}
