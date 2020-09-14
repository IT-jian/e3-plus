<?php


namespace App\Services\Platform\Taobao\Qimen;


interface ApiContracts
{
    /**
     * 执行请求
     *
     * @param $request
     * @return mixed
     *
     * @author linqihai
     * @since 2020/3/23 11:45
     */
    public function execute($request);
}