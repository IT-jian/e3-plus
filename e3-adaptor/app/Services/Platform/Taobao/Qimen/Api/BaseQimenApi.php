<?php


namespace App\Services\Platform\Taobao\Qimen\Api;


class BaseQimenApi
{
    public function success($message = 'success')
    {
        return [
            'status'  => true,
            'message' => $message,
        ];
    }

    public function fail($message)
    {
        return [
            'status'  => false,
            'message' => $message,
        ];
    }
}