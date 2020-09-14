<?php


namespace App\Channels;


use Illuminate\Notifications\Notification;

/**
 * 钉钉日志通知
 *
 * Class DingtalkLogChannel
 * @package App\Channels
 *
 * @author linqihai
 * @since 2020/3/25 9:39
 */
class DingtalkLogChannel
{
    /**
     * 钉钉通知 -- 记录在日志.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toDingtalkLog($notifiable);
        \Log::info('DingtalkLogChannel --->', $message->getMessage());
    }
}