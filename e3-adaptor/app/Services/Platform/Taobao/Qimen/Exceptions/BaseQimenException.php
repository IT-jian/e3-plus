<?php


namespace App\Services\Platform\Taobao\Qimen\Exceptions;


class BaseQimenException extends \RuntimeException
{
    public $errorCode = '512';
    public $errorMsg = 'modify-address-failed';
    public $responseBody;
    protected $exceptionMap = array(
        '401'  => 'sign-check-failure',
        '511'  => 'modify-address-forbid',
        '512'  => 'modify-address-failed',
        '513'  => 'part-sub-order-failed',
        '1001' => '已发货拦截失败',
        '1002' => '已出库拦截失败',
        '1003' => '已配货拦截失败',
        '1004' => '已拣货拦截失败',
        '1005' => '在途中拦截失败',
        '1006' => '库存占用拦截失败',
        '1007' => '未获取订单拦截失败',
        '1008' => '未定义异常失败',
        '1009' => '奇门接口调用异常',
        '1110' => '地址格式有误，请联系商家修改',
        '1106' => '订单已安排发货无法修改，请联系商家修改，订单已进入出库环节',
        '1107' => '订单已安排发货无法修改，请联系商家修改，订单已完成',
        '1111' => '订单处于异常状态无法修改，请联系商家修改',
        '1113' => '订单处理中不支持修改，请联系商家修改',
        '1115' => '修改失败，请联系商家修改',

    );

    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
        if (isset($this->exceptionMap[$errorCode])) {
            $this->errorMsg = $this->exceptionMap[$errorCode];
        }
    }

    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

    public function setErrorMsg($errorMsg)
    {
        $this->errorMsg = $errorMsg;
    }

    public function setResponseBody($responseBody)
    {
        $this->responseBody = $responseBody;
    }
}