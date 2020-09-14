<?php


namespace App\Api\Helpers;


class AdaptorResultSet
{
    public $status = true;

    public $message = '成功';

    public $data = [];

    /**
     * AdaptorResultSet constructor.
     * @param bool $status
     * @param string $message
     * @param array $data
     */
    public function __construct($status = true, $message = '成功', $data = [])
    {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
    }
}