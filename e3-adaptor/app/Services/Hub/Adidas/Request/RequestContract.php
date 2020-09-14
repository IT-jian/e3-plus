<?php


namespace App\Services\Hub\Adidas\Request;


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
     * 设置格式化完成的内容
     *
     * @param $params
     * @return mixed
     *
     * @author linqihai
     * @since 2020/05/25 14:45
     */
    public function setFormatContent($params);

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