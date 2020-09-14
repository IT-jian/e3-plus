<?php

namespace App\Exceptions;

use App\Api\Helpers\ExceptionReport;
use App\Jobs\DingTalkNoticeTextSendJob;
use Cache;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        MaxAttemptsExceededException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        // redis 异常报警
        if ($exception instanceof \RedisException) {
            if (Str::contains($exception->getMessage(), ['NOAUTH'])) {
                $noticeKey = 'redis_exception_notice_send_at';
                $lastSendAt = Cache::store('file')->get($noticeKey, null);
                if (empty($lastSendAt)) {
                    $lastSendAt = time();
                    Cache::store('file')->set($noticeKey, $lastSendAt, 60);
                    $params = [
                        'message' => '发现Redis连接异常，请注意处理：' . $exception->getMessage(),
                        'isAtAll' => true
                    ];
                    dispatch_now(new DingTalkNoticeTextSendJob($params));
                }
            }
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render($request, Exception $exception)
    {
        $reporter = ExceptionReport::make($request, $exception);
        if ($reporter->shouldReturn()){
            return $reporter->report();
        }
        return parent::render($request, $exception);
    }
}
