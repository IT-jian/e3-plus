<?php

namespace App\Http\Middleware;


use App\Models\HubApiLog;
use App\Models\SkuInventoryApiLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class HubApiLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $start = Carbon::now();

        $response = $next($request);

        $content = json_decode($request->getContent(), true);
        $log = [
            'api_method'   => $content['method'] ?? '',
            'ip' => $request->getClientIp(),
            'start_at' => $start->toDateTimeString(),
            'end_at' => Carbon::now()->toDateTimeString(),
            'partner'   => $request->headers->get('customer', ''),
            'platform'   => $request->headers->get('maketplace-type', ''),
            'input'   => $request->getContent(),
            'response' => json_encode($response)
        ];
        // 取得唯一请求ID
        $log['request_id'] = Uuid::uuid1()->toString($log['api_method']);
        if ('database' == env('HUB_API_LOG_TYPE', 'database')) {
            try {
                if (in_array(trim($log['api_method']), ['e3plus.oms.items.stock.update', 'e3plus.oms.items.stock.async.update'])) {
                    SkuInventoryApiLog::insert($log);
                } else {
                    HubApiLog::insert($log);
                }
            } catch (\Exception $e) {
                \Log::channel('hub_api')->info($e->getMessage(), $log);
            }
        } else {
            \Log::channel('hub_api')->info('', $log);
        }
        // 设置响应头
        $response->headers->set('Adaptor-Request-Id', $log['request_id']);
        unset($content, $start, $log);

        return $response;
    }
}
