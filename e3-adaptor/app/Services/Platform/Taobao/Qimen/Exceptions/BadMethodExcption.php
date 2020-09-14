<?php


namespace App\Services\Platform\Taobao\Qimen\Exceptions;


class BadMethodExcption extends BaseQimenException
{
    public function __construct(string $message = "修改失败，请联系商家修改", int $code = 1115, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}