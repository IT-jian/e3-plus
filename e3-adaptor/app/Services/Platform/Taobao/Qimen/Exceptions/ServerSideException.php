<?php


namespace App\Services\Platform\Taobao\Qimen\Exceptions;


class ServerSideException extends BaseQimenException
{
    public function __construct(string $message = "modify-address-failed", int $code = 512, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}