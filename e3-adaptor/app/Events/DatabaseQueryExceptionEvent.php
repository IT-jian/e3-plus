<?php

namespace App\Events;

/**
 * 数据库连接异常事件
 *
 * Class DatabaseQueryExceptionEvent
 * @package App\Events
 *
 * @author linqihai
 * @since 2020/3/24 16:53
 */
class DatabaseQueryExceptionEvent extends Event
{
    public $exception;

    /**
     * DatabaseQueryExceptionEvent constructor.
     * @param $exception
     */
    public function __construct($exception)
    {

        $this->exception = $exception;
    }
}
