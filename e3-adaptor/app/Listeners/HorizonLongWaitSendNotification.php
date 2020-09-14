<?php

namespace App\Listeners;

use App\Notifications\DingtalkNotification;
use Calchen\LaravelDingtalkRobot\Message\MarkdownMessage;
use Calchen\LaravelDingtalkRobot\Robot;
use Illuminate\Support\Facades\Notification;
use Laravel\Horizon\Events\LongWaitDetected;

class HorizonLongWaitSendNotification
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
    public function handle(LongWaitDetected $event)
    {
        // 没配置 token 不处理通知
        $hasToken = config("dingtalk_robot.{$this->robtName}.access_token", '');
        if (empty($hasToken)) {
            \Log::info('queue long wait detected', [$event->connection, $event->queue, $event->seconds]);

            return;
        }
        $queue = $event->queue;
        $seconds = $event->seconds;
        $connection = $event->connection;
        $title = config('app.name') . '[' . config('app.env') . ']: Long Queue Wait Detected';
        $line = sprintf(
            'The "**%s**" queue on the "**%s**" connection has a wait time of **%s** seconds.',
            $queue, $connection, $seconds
        );

        $text = "#### Long Queue Wait Detected \n" .
            "##### App：" . config('app.name') . " Env：" . config('app.env') . "\n\n" .
            "> {$line}\n";
        $msg = new MarkdownMessage($title, $text);
        // new Robot($name) $name 为 config 中配置的内容
        Notification::send(new Robot(), new DingtalkNotification($msg));

        return;
    }
}
