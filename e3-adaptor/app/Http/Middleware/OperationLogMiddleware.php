<?php


namespace App\Http\Middleware;


use App\Models\OperationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OperationLogMiddleware
{
    /**
     * 不进行记录的字段
     * @var array
     */
    public $exceptFields = ['password', 'secret'];
    /**
     * Handle an incoming request.
     * 记录日志
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $response = $next($request);
        if ($this->shouldLogOperation($request)) {
            $log = [
                'user_id' => $request->user()->id ?? '0',
                'path'    => $request->path(),
                'method'  => $request->method(),
                'ip'      => $request->getClientIp(),
                'input'   => $request->except($this->exceptFields),
                'response' => $response
            ];
            OperationLog::create($log);
        }

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function shouldLogOperation(Request $request)
    {
        if (empty($request->user()->id)) {
            return false;
        }

        if ($this->inExceptArray($request)) {
            return false;
        }

        return in_array($request->method(), ['PUT', 'POST', 'PATH', 'DELETE']);
    }

    /**
     * Determine if the request has a URI that should pass through Logs.
     * 决定哪些操作不进行记录
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function inExceptArray($request)
    {
        $exceptList = [
            'api/operation_logs/*'
        ];
        foreach ($exceptList as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            $methods = [];

            if (Str::contains($except, [':'])) {
                list($methods, $except) = explode(':', $except);
                $methods = explode(',', $methods);
            }

            $methods = array_map('strtoupper', $methods);

            if ($request->is($except) &&
                (empty($methods) || in_array($request->method(), $methods))) {
                return true;
            }
        }

        return false;
    }
}
