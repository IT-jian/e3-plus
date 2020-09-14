<?php


namespace App\Console\Commands;


use App\Facades\HubApi;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RetryHubApiRequestCommand extends Command
{
    protected $name = 'adaptor:hub_api_request:retry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重试请求次数';

    public function handle()
    {
        $retryAt = Carbon::now()->timestamp;
        $count = $this->model()->whereIn('status', [0, 3])->where('retry_at', '<=', $retryAt)->count();
        if (empty($count)) {

            return true;
        }

        $requests = $this->model()->whereIn('status', [0, 3])->where('retry_at', '<=', $retryAt)->orderByDesc('id')->limit(1000)->get();
        $successIds = [];
        foreach ($requests as $request) {
            $customer = $request['customer'] ?? 'adidas';
            $platform = $request['platform'] ?? 'taobao';
            $method = $request['method'];
            $content = json_decode($request['content'], true);
            try {
                $result = HubApi::hub($customer)->platform($platform)->execute(compact('method', 'content'));
                $successIds[] = $request['id'];
            } catch (\Exception $e) {
                $update = [
                    'retry_at' => Carbon::now()->addHours(2)->toDateTimeString(),
                    'status' => 3,
                    'message' => $e->getMessage(),
                ];
                $this->model()->where('id', $request['id'])->update($update);
            }
        }
        if ($successIds) {
            $this->model()->whereIn('id', $successIds)->update(['status' => 1]);
        }

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
