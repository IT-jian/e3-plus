<?php


namespace App\Services\Hub\Adidas\ResponseParser;


class BaseParser
{
    /**
     * @var $status bool 状态
     */
    protected $status;

    /**
     * @var $message string
     */
    protected $message;

    /**
     * @var $errorCode string
     */
    protected $errorCode;

    /**
     * @var $data array|mixed
     */
    protected $data;

    /**
     * @var $response \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    public function formatResponse($status = false, $message = 'parse fail', $data = [])
    {
        return [
            'status'  => $status,
            'message' => $message,
            'data'    => $data,
        ];
    }

    public function initResponse()
    {
        $this->setStatus(false);
        $this->setData([]);
        $this->setMessage('parse json response fail');
        $this->setErrorCode(null);
    }
    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse(): \Psr\Http\Message\ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return $this
     */
    public function setResponse(\Psr\Http\Message\ResponseInterface $response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * 请求是否成功
     *
     * @return bool
     */
    public function isSuccess()
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function isStatus(): bool
    {
        return $this->status;
    }

    /**
     * @param bool $status
     */
    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * @param string $errorCode
     */
    public function setErrorCode(string $errorCode): void
    {
        $this->errorCode = $errorCode;
    }

    /**
     * @return array|mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array|mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }
}