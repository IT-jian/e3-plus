<?php

namespace App\Jobs;

use App\Notifications\DingtalkNotification;
use Calchen\LaravelDingtalkRobot\Message\TextMessage;
use Calchen\LaravelDingtalkRobot\Robot;
use Illuminate\Support\Facades\Notification;

class DingTalkNoticeTextSendJob extends Job
{
    private $params;

    public $queue = 'default';

    private $robtName = 'default';

    /**
     * 钉钉消息通知任务
     *
     * DingTalkNoticeTextSendJob constructor.
     * @param $params
     */
    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return bool|void
     */
    public function handle()
    {
        $message = $this->params['message'] ?? '';
        if (empty($message)) {
            return true;
        }
        // 没配置 token 不处理通知
        $hasToken = config("dingtalk_robot.{$this->robtName}.access_token", '');
        if (empty($hasToken)) {
            \Log::info('DingTalkNoticeTextSendJob', $this->params);

            return;
        }

        $message = config('app.name') . '[' . config('app.env') . ']: ' . $message;

        $msg = new TextMessage($message);
        if ($this->params['isAtAll'] ?? false) {
            $msg = $msg->atAll();
        }
        Notification::send(new Robot($this->robtName), new DingtalkNotification($msg));

        return true;
    }
}
