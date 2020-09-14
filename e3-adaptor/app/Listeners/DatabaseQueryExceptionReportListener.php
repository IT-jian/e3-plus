<?php

namespace App\Listeners;

use App\Events\DatabaseQueryExceptionEvent;
use App\Notifications\DingtalkNotification;
use Calchen\LaravelDingtalkRobot\Message\MarkdownMessage;
use Calchen\LaravelDingtalkRobot\Robot;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redis;

class DatabaseQueryExceptionReportListener
{
    public $robtName = 'default';

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param LongWaitDetected $event
     * @return void
     */
    public function handle(DatabaseQueryExceptionEvent $event)
    {
        /**
         * @var QueryException $exception
         */
        $exception = $event->exception;
        // 每 300 秒，同一个sql报错仅允许一个
        Redis::throttle(sha1($exception->getSql()))->allow(1)->every(5 * 60)->then(function () use ($exception) {
            // 任务逻辑...
            $title = config('app.name') . '[' . config('app.env') . ']: Query Exception Found';

            $message = "Exception Code: " . $exception->getCode() . ", Message:" . $exception->getMessage();
            $file = sprintf(
                'Exception Found In File "**%s**":"**%s**" .',
                $exception->getFile(), $exception->getLine()
            );
            $text = "#### Query Exception Found \n" .
                "##### App：" . config('app.name') . " Env：" . config('app.env') . "\n\n" .
                "> {$message}\n" .
                "> {$file}\n";
            // 没配置 token 不处理通知
            $hasToken = config("dingtalk_robot.{$this->robtName}.access_token", '');
            if (empty($hasToken)) {

                \Log::info($text);

                return;
            }
            $msg = new MarkdownMessage($title, $text);
            // new Robot($name) $name 为 config 中配置的内容
            Notification::send(new Robot(), new DingtalkNotification($msg));
        }, function () {
            // 无法获得锁...
            // \Log::debug('can not get lock ');
        });

        return;
    }
}
