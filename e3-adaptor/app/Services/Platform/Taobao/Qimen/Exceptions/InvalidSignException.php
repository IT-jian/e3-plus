<?php


namespace App\Services\Platform\Taobao\Qimen\Exceptions;


class InvalidSignException extends BaseQimenException
{
    public function __construct(string $message = "sign-check-failure", int $code = 401, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}