<?php

namespace App\Notifications;

use App\Channels\DingtalkLogChannel;
use Calchen\LaravelDingtalkRobot\DingtalkRobotChannel;
use Calchen\LaravelDingtalkRobot\Message\Message;
use Calchen\LaravelDingtalkRobot\Robot;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DingtalkNotification extends Notification
{
    // 注意这里如果不需要异步可不使用 Queueable Trait
    use Queueable;
    /**
     * @var Message
     */
    private $message;

    /**
     * DingtalkNotification constructor.
     *
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * 这里的 channel 必须包含 DingtalkRobotChannel 才能正常的发送消息
     *
     * @param $notifiable
     * @return array
     *
     * @author linqihai
     * @since 2020/3/25 9:28
     */
    public function via($notifiable)
    {
        // 校验是否设置了token，如果没设置，则使用 log 保存通知
        $hasToken = config("dingtalk_robot.{$notifiable->getName()}.access_token", '');
        if (empty($hasToken)) {
            return [DingtalkLogChannel::class];
        }

        return [DingtalkRobotChannel::class];
    }

    /**
     * @param Robot $notifiable
     *
     * @return Message
     */
    public function toDingTalkRobot($notifiable)
    {
        $message = $this->message;

        $message->setRobot($notifiable->getName());

        return $message;
    }

    /**
     * 日志通知
     *
     * @param Robot $notifiable
     * @return Message
     *
     * @author linqihai
     * @since 2020/3/25 9:37
     */
    public function toDingtalkLog($notifiable)
    {
        $message = $this->message;

        $message->setRobot($notifiable->getName());

        return $message;
    }
}