<?php

namespace App\Http\Controllers\Admin;

use App\Models\PlatformDownloadConfig;
use App\Models\ShopDownloadConfig;
use App\Models\Sys\Shop;
use App\Services\ShopDownloadConfigServer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 店铺下载配置
 * Class ShopDownloadConfigController
 * @package App\Http\Controllers\Admin
 */
class ShopDownloadConfigController extends Controller
{
    /**
     * Display a listing of the 店铺下载配置.
     * GET|HEAD /shop_download_config
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $where = [];
        if ($shopCode = $request->get('shop_code')) {
            $where['shop_code'] = $shopCode;
        }

        if ($shopCode = $request->get('shop')) {
            $shop = Shop::where('code', $shopCode)->first();
            $where['shop_code'] = $shop['code'];
        }

        if ($code = $request->get('code')) {
            $where['code'] = $code;
        }

        if ($name = $request->get('name')) {
            $where['name'] = $name;
        }

        if ($stopDownload = $request->get('stop_download')) {
            $where['stop_download'] = $stopDownload;
        }

        if ($queryPageSize = $request->get('query_page_size')) {
            $where['query_page_size'] = $queryPageSize;
        }

        if ($jobPageSize = $request->get('job_page_size')) {
            $where['job_page_size'] = $jobPageSize;
        }
        $shopDownloadConfigs = ShopDownloadConfig::where($where)->orderBy('shop_code')
                    ->paginate($request->get('perPage', 15));

        return $shopDownloadConfigs;
    }

    /**
     * Store a newly created 店铺下载配置 in storage.
     * POST /shop_download_config
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $this->validate($request, ['shop_code' => 'required']);

        $shopCode = $request->input('shop_code');
        $type = $request->input('type', 2);
        $shop = Shop::where('code', $shopCode)->firstOrFail();
        $platformConfigs = PlatformDownloadConfig::where('type', $type)->where('platform', $shop['platform'])->get()->toArray();
        $existConfigs = [];
        $shopConfigs = ShopDownloadConfig::select(['code'])->where('type', $type)->where('shop_code', $shopCode)->where('platform', $shop['platform'])->get();
        if ($shopConfigs->isNotEmpty()) {
            $existConfigs = $shopConfigs->pluck('code')->toArray();
        }

        foreach ($platformConfigs as $platformConfig) {
            if ($existConfigs && in_array($platformConfig['code'], $existConfigs)) {
                continue;
            }
            unset($platformConfig['id']);
            $shopConfig = new ShopDownloadConfig();
            $shopConfig->fill($platformConfig);
            $shopConfig->shop_code = $shopCode;
            $shopConfig->next_query_at = Carbon::now()->toDateTimeString();
            $shopConfig->save();
            $configServer = new ShopDownloadConfigServer($shopConfig->code, $shopConfig->shop_code, 1);
            $configServer->setNextQueryAtLock(strtotime($shopConfig->next_query_at));
        }

        return $this->setStatusCode(Response::HTTP_CREATED)->respond([]);
    }

    /**
     * Display the specified 店铺下载配置.
     * GET|HEAD /shop_download_config/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $shopDownloadConfig = ShopDownloadConfig::findOrFail($id);

        return $this->respond($shopDownloadConfig);
    }

    /**
     * Update the specified 店铺下载配置 in storage.
     * PUT/PATCH /shop_download_config/{id}
     *
     * @param  int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request,$id)
    {
        $rules = ShopDownloadConfig::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $shopDownloadConfig = ShopDownloadConfig::findOrFail($id);
        $configServer = new ShopDownloadConfigServer($shopDownloadConfig->code, $shopDownloadConfig->shop_code);
        if (!empty($input['next_query_at'])) {
            $result = $configServer->setNextQueryAtLock(strtotime($input['next_query_at']));
            if (!$result['status']) {
                return $this->failed($result['message']);
            }
        }
        $shopDownloadConfig->fill($input)->save();
        $configServer->removeConfigCache();


        return $this->respond($shopDownloadConfig);
    }

    /**
     * Remove the specified 店铺下载配置 from storage.
     * DELETE /shop_download_config/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $shopDownloadConfig = ShopDownloadConfig::findOrFail($id);
        $shopDownloadConfig->delete();
        try {
            $configServer = new ShopDownloadConfigServer($shopDownloadConfig->code, $shopDownloadConfig->shop_code);
            $configServer->removeConfigCache();
        } catch (\Exception $e) {
        }

        return $this->success([]);
    }

}
