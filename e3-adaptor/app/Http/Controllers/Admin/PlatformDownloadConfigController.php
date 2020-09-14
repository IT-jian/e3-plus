<?php

namespace App\Http\Controllers\Admin;

use App\Models\PlatformDownloadConfig;
use App\Services\PlatformDownloadConfigServer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 平台下载配置
 * Class PlatformDownloadConfigController
 * @package App\Http\Controllers\Admin
 */
class PlatformDownloadConfigController extends Controller
{
    /**
     * Display a listing of the 平台下载配置.
     * GET|HEAD /platform_download_config
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $where = [];
        if ($code = $request->get('code')) {
            $where['code'] = $code;
        }

        if ($name = $request->get('name')) {
            $where['name'] = $name;
        }

        if ($platform = $request->get('platform')) {
            $where['platform'] = $platform;
        }

        $stopDownload = $request->get('stop_download', '');
        if (isset($stopDownload) && '' != $stopDownload) {
            $where['stop_download'] = $stopDownload;
        }

        if ($queryPageSize = $request->get('query_page_size')) {
            $where['query_page_size'] = $queryPageSize;
        }

        if ($jobPageSize = $request->get('job_page_size')) {
            $where['job_page_size'] = $jobPageSize;
        }
        $platformDownloadConfigs = PlatformDownloadConfig::where($where)
            ->paginate($request->get('perPage', 15));

        return $platformDownloadConfigs;
    }

    /**
     * Store a newly created 平台下载配置 in storage.
     * POST /platform_download_config
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = PlatformDownloadConfig::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $platformDownloadConfig = PlatformDownloadConfig::create($input);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($platformDownloadConfig);
    }

    /**
     * Display the specified 平台下载配置.
     * GET|HEAD /platform_download_config/{id}
     *
     * @param int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $platformDownloadConfig = PlatformDownloadConfig::findOrFail($id);

        return $this->respond($platformDownloadConfig);
    }

    /**
     * Update the specified 平台下载配置 in storage.
     * PUT/PATCH /platform_download_config/{id}
     *
     * @param int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request, $id)
    {
        $rules = PlatformDownloadConfig::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $platformDownloadConfig = PlatformDownloadConfig::findOrFail($id);
        $configServer = new PlatformDownloadConfigServer($platformDownloadConfig->code);
        if (!empty($input['next_query_at'])) {
            $result = $configServer->setNextQueryAtLock(strtotime($input['next_query_at']));
            if (!$result['status']) {
                return $this->failed($result['message']);
            }
        }
        $platformDownloadConfig->fill($input)->save();
        $configServer->removeConfigCache();

        return $this->respond($platformDownloadConfig);
    }

    /**
     * Remove the specified 平台下载配置 from storage.
     * DELETE /platform_download_config/{id}
     *
     * @param int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $platformDownloadConfig = PlatformDownloadConfig::findOrFail($id);

        $platformDownloadConfig->delete();

        return $this->success([]);
    }

}
