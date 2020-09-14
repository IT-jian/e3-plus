<?php


namespace App\Api\Helpers;
use Symfony\Component\HttpFoundation\Response as FoundationResponse;

trait ApiResponse
{
    /**
     * @var int
     */
    protected $statusCode = FoundationResponse::HTTP_OK;
    /**
     * [$headers 头部]
     * @var array
     */
    protected $headers = [];

    protected $subData = [];

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
    /**
     * @param $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }
    /**
     * [getHeaders description]
     * @return [type] [description]
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    /**
     * @param $headers
     * @return $this
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    public function setSubData($data)
    {
        $this->subData = $data;
    }
    /**
     * @param $data
     * @param array $header
     * @return mixed
     */
    public function respond($data, $headers = [])
    {
        // $headers['Access-Control-Allow-Origin'] = '*';
        if (!empty($headers)) {
            $this->setHeaders($headers);
        }
        if ($this->subData) {
            $data['data'] = isset($data['data']) ? array_merge($this->subData, $data['data']) : $this->subData;
        }

        return response()->json($data, $this->getStatusCode(), $headers);
    }

    /**
     * @param $status
     * @param array $data
     * @param null $code
     * @return mixed
     */
    public function status($status, array $data, $code = null)
    {
        if ($code) {
            $this->setStatusCode($code);
        }
        $status = [
            'status' => $status,
            'code'   => $this->statusCode,
        ];
        $data = array_merge($status, $data);
        return $this->respond($data);
    }

    /**
     * @param $message
     * @param int $code
     * @param string $status
     * @return mixed
     */
    public function failed($message, $code = FoundationResponse::HTTP_BAD_REQUEST, $status = 'api-server-exception')
    {
        if ($code < 500) {
            $status = 'api-invalid-parameter';
        }

        return $this->setStatusCode($code)->message($message, $status);
    }
    /**
     * @param $message
     * @param string $status
     * @param array $subData
     * @return mixed
     */
    public function message($message, $status = "api-success")
    {
        return $this->status($status, [
            'message' => $message,
        ]);
    }
    /**
     * @param string $message
     * @return mixed
     */
    public function internalError($message = "Internal Error!")
    {
        return $this->failed($message, FoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
    /**
     * @param string $message
     * @return mixed
     */
    public function created($message = "created")
    {
        return $this->setStatusCode(FoundationResponse::HTTP_CREATED)
            ->message($message);
    }
    /**
     * @param $data
     * @param string $status
     * @return mixed
     */
    public function success($data, $status = "api-success")
    {
        return $this->status($status, compact('data'));
    }
    /**
     * @param string $message
     * @return mixed
     */
    public function notFond($message = 'Not Fond!')
    {
        return $this->failed($message, Foundationresponse::HTTP_NOT_FOUND);
    }
}