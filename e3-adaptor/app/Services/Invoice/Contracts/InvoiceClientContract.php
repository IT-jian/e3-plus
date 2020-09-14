<?php


namespace App\Services\Invoice\Contracts;


interface InvoiceClientContract
{
    public function fetchApply($tid);

    public function requestOmini($tid);

    public function create();

    /**
     * @param $name
     * @return \App\Services\HubApi\Contracts\HubPlatformApiContract
     *
     * @author linqihai
     * @since 2019/12/31 18:20
     */
    public function platform($name);
}
