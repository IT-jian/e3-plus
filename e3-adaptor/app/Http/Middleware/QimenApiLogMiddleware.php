<?php

namespace App\Http\Middleware;


use App\Models\HubApiLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class QimenApiLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $start = Carbon::now();

        $response = $next($request);

        $query = $request->all();
        $content = json_decode($request->getContent(), true);
        $log = [
            'api_method' => $query['method'] ?? '',
            'ip'         => $request->getClientIp(),
            'start_at'   => $start->toDateTimeString(),
            'end_at'     => Carbon::now()->toDateTimeString(),
            'partner'    => 'qimen',
            'platform'   => 'taobao',
            'input'      => json_encode(
                [
                    'content' => $content,
                    'query'   => $query,
                ]
            ),
            'response'   => json_encode($response),
        ];
        // 取得唯一请求ID
        $log['request_id'] = Uuid::uuid1()->toString($log['api_method']);
        if ('database' == env('HUB_API_LOG_TYPE', 'database')) {
            HubApiLog::insert($log);
        } else {
            \Log::channel('hub_api')->info('', $log);
        }
        // 设置响应头
        $response->headers->set('Adaptor-Request-Id', $log['request_id']);
        unset($content, $start, $log);

        return $response;
    }
}
