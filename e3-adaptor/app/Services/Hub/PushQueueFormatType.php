<?php


namespace App\Services\Hub;


class PushQueueFormatType
{
    const WHEN_PUSH_TO_QUEUE = '2';
    const WHEN_POP_FROM_QUEUE = '1';

    public static function is($type)
    {
        return $type == config('hubclient.push_queue_format_type') ? true : false;
    }
}