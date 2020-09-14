<?php


namespace App\Services\Platform\Taobao\Client\Top\Exceptions;


use App\Services\Platform\Exceptions\PlatformServerSideException;

class TaobaoTopServerSideException extends PlatformServerSideException
{
    protected $subErrorCode;
    protected $subErrorMessage;

    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return mixed
     */
    public function getSubErrorCode()
    {
        return $this->subErrorCode;
    }

    public function setSubErrorCode($subErrorCode)
    {
        $this->subErrorCode = $subErrorCode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubErrorMessage()
    {
        return $this->subErrorMessage;
    }

    public function setSubErrorMessage($subErrorMessage)
    {
        $this->subErrorMessage = $subErrorMessage;

        return $this;
    }
}