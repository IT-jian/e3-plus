<?php


namespace App\Services\Adaptor;


use App\Services\Adaptor\Contracts\AdaptorContract;

class TaobaoAdaptor extends BaseAdaptor implements AdaptorContract
{
    /**
     * 平台名称，用于解析类
     *
     * @return string
     *
     * @author linqihai
     * @since 2020/1/10 10:55
     */
    public function platformType()
    {
        return 'taobao';
    }
}