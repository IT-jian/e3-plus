<?php


namespace App\Services\HubApi;


use App\Services\HubApi\Adidas\AdidasApiManager;
use App\Services\HubApi\Contracts\HubApiContract;
use Illuminate\Http\Request;

class AdidasHubApi implements HubApiContract
{
    /**
     * @var AdidasApiManager
     */
    private $hubApi;

    public function __construct(AdidasApiManager $hubApi)
    {
        $this->hubApi = $hubApi;
    }

    /**
     * 全局的校验请求内容及参数
     *
     * @param $request
     *
     * @author linqihai
     * @since 2019/12/30 15:30
     */
    public function check(Request $request)
    {
    }

    public function platform($name)
    {
        return $this->hubApi->platform($name);
    }
}