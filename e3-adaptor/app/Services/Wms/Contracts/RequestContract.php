<?php


namespace App\Services\Wms\Contracts;


interface RequestContract
{
    /**
     * 返回 接口名称
     * @return string
     *
     * @author linqihai
     * @since 2020/1/14 16:23
     */
    public function getApiMethodName();

    /**
     * 请求关键识别内容，用于日志查询
     *
     * @return mixed
     *
     * @author linqihai
     * @since 2020/1/14 16:23
     */
    public function getKeyword();

    /**
     * 处理内容
     *
     * @param $data
     * @return mixed
     *
     * @author linqihai
     * @since 2020/1/14 16:24
     */
    public function setContent($data);

    /**
     * 格式化请求body
     * @return mixed
     *
     * @author linqihai
     * @since 2020/1/14 16:24
     */
    public function getBody();

    public function getDataVersion();

    public function responseCallback($response);
}
