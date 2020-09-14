<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdidasItem;
use App\Services\Hub\Adidas\AdidasClient;
use App\Services\Hub\Adidas\Request\TradeCreateRequest;
use App\Services\Platform\HttpClient\GuzzleAdapter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Adidas 请求模拟
 *
 * Class AdidasItemController
 * @package App\Http\Controllers\Admin
 */
class AdidasRequestSimulationController extends Controller
{
    public function store(Request $request)
    {
        $config = config('hubclient.clients.adidas');

        if ($request->filled('url')) {
            $config['url'] = $request->input('url');
        }
        if ($request->filled('simulation')) {
            $config['simulation'] = 'true' == $request->input('simulation') ? '1' : '0';
        }
        if ($request->filled('app_env')) {
            $config['app_env'] = $request->input('app_env');
        }
        $apiName = $request->input('api_name');
        $data = $request->input('content');
        $apiRequest = new TradeCreateRequest();
        if ($request->input('api_path')) {
            $apiRequest->apiPath = $request->input('api_path');
        }
        $apiRequest->setApiName($apiName);
        $apiRequest->setData($data);
        $apiRequest->keyword = 'adidas-simulation';
        $adidasClient = new AdidasClient($config);

        $result = $adidasClient->execute($apiRequest);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($result);
    }

    public function index(Request $request)
    {

        $config = config('hubclient.clients.adidas');

        $default = [
            'app_env' => $config['app_env'] == 'jd' ? 'jd' : 'tmall',
            'api_path' => $config['url'],
            'simulation' => 1 == $config['simulation'] ? true : false,
            'api_name' => 'adidas/omnihub/createorder',
        ];

        return $this->success($default);
    }
}
