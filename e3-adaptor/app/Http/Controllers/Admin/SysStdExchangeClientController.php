<?php

namespace App\Http\Controllers\Admin;

use App\Facades\HubClient;
use App\Models\SysStdExchange;
use App\Models\SysStdPushConfig;
use App\Services\Hub\HubRequestEnum;

/**
 * 标准换货单 hub client 操作
 *
 * Class SysStdExchangeClientController
 * @package App\Http\Controllers\Admin
 */
class SysStdExchangeClientController extends Controller
{
    public function getProxy($method)
    {
        $pushConfig = (new SysStdPushConfig())->methodMapCache($method);

        return !empty($pushConfig['proxy']) ? $pushConfig['proxy'] : null;
    }

    protected function getRequest($method, $content)
    {
        $request = HubClient::hub($this->getProxy($method))->resolveRequestClass($method);

        return $request->setContent($content);
    }

    public function push($id)
    {
        $sysStdExchange = SysStdExchange::findOrFail($id);
        if (cutoverTrade($sysStdExchange['tid'], $sysStdExchange['platform'])) {
            $request = $this->getRequest(HubRequestEnum::EXCHANGE_CREATE_EXTEND, $sysStdExchange->toArray());
            $result = HubClient::hub($this->getProxy(HubRequestEnum::EXCHANGE_CREATE_EXTEND))->execute($request);
        } else {
            $request = $this->getRequest(HubRequestEnum::EXCHANGE_CREATE, $sysStdExchange->toArray());
            $result = HubClient::hub($this->getProxy(HubRequestEnum::EXCHANGE_CREATE))->execute($request);
        };
        if (!$result['status']) {
            return $this->failed($result['message']);
        }
        return $this->success($result);
    }

    public function pushFormat($id)
    {
        $sysStdExchange = SysStdExchange::findOrFail($id);
        if (cutoverTrade($sysStdExchange['tid'], $sysStdExchange['platform'])) {
            $method = HubRequestEnum::EXCHANGE_CREATE_EXTEND;
        } else {
            $method = HubRequestEnum::EXCHANGE_CREATE;
        }
        $request = $this->getRequest($method, $sysStdExchange->toArray());
        $result = $request->getBody();

        return $this->respond($result);
    }

    public function pushCancel($id)
    {
        $sysStdExchange = SysStdExchange::findOrFail($id);
        $request = $this->getRequest(HubRequestEnum::EXCHANGE_CANCEL, $sysStdExchange->toArray());
        $result = HubClient::hub($this->getProxy(HubRequestEnum::EXCHANGE_CANCEL))->execute($request);
        if (!$result['status']) {
            return $this->failed($result['message']);
        }
        return $this->success($result);
    }

    public function pushCancelFormat($id)
    {
        $sysStdExchange = SysStdExchange::findOrFail($id);
        $request = $this->getRequest(HubRequestEnum::EXCHANGE_CANCEL, $sysStdExchange->toArray());
        $result = $request->getBody();

        return $this->respond($result);
    }

    public function pushLogistic($id)
    {
        $sysStdExchange = SysStdExchange::findOrFail($id);
        $request = $this->getRequest(HubRequestEnum::EXCHANGE_RETURN_LOGISTIC_MODIFY, $sysStdExchange->toArray());
        $result = HubClient::hub($this->getProxy(HubRequestEnum::EXCHANGE_RETURN_LOGISTIC_MODIFY))->execute($request);
        if (!$result['status']) {
            return $this->failed($result['message']);
        }
        return $this->success($result);
    }

    public function pushLogisticFormat($id)
    {
        $sysStdExchange = SysStdExchange::findOrFail($id);
        $request = $this->getRequest(HubRequestEnum::EXCHANGE_RETURN_LOGISTIC_MODIFY, $sysStdExchange->toArray());
        $result = $request->getBody();

        return $this->respond($result);
    }
}
