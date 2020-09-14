<?php


namespace App\Services\HubApi;


use App\Services\Platform\Exceptions\PlatformClientSideException;

class BaseApi
{
    const ERROR_CODE_FAIL = 400;
    const ERROR_CODE_RETRY = 500;

    protected $data;

    protected $notNullFields = []; // 请求必填参数

    /**
     * 请求内容
     *
     * @param $data
     *
     * @author linqihai
     * @since 2019/12/31 16:15
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * 参数校验
     *
     * @return bool
     *
     * @author linqihai
     * @since 2019/12/31 16:15
     */
    public function check()
    {
        $this->dataValidate();

        return true;
    }

    /**
     * 代理转发
     *
     * @return array
     *
     * @author linqihai
     * @since 2019/12/31 16:15
     */
    public function proxy()
    {
        return $this->success();
    }

    public function success($data = [], $message = 'success')
    {
        return [
            'status' => 1,
            'error_code' => 0,
            'data' => $data,
            'message' => $message,
        ];
    }

    public function fail($data = [], $message = 'fail')
    {
        $errorCode = $this->parseErrorCode($data);
        return [
            'status' => 0,
            'data' => $data,
            'error_code' => empty($errorCode) ? self::ERROR_CODE_FAIL : $errorCode,
            'message' => $message,
        ];
    }

    /**
     * 处理错误码
     *
     * @param $data
     * @return int
     */
    public function parseErrorCode($data)
    {
        return self::ERROR_CODE_FAIL;
    }

    public function responseSimple($response)
    {
        if ($this->isSuccess($response)) {
            return $this->success();
        }

        return $this->fail($response);
    }

    public function isSuccess($response)
    {
        return true;
    }

    /**
     * 校验数据
     *
     * @return bool
     */
    public function dataValidate()
    {
        foreach ($this->getNotNullFields() as $fieldName) {
            $value = data_get($this->data, $fieldName, null);
            if (self::checkEmpty($value)) {
                throw new PlatformClientSideException("adaptor-check-error:Missing Required Arguments:" .$fieldName , 422);
            }
        }

        return true;
    }

    /**
     * 校验值是否为空
     *
     * @param $value
     * @return bool
     */
    public static function checkEmpty($value)
    {
        if ($value === null) {
            return true;
        }
        if (is_string($value) && trim($value) === "") {
            return true;
        }
        if (is_array($value) && count($value) == 0) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getNotNullFields(): array
    {
        return $this->notNullFields;
    }

    /**
     * @param array $notNullFields
     */
    public function setNotNullFields($notNullFields)
    {
        $this->notNullFields = $notNullFields;
    }
}
