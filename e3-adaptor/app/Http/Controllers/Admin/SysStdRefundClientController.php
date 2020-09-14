<?php

namespace App\Http\Controllers\Admin;

use App\Facades\HubClient;
use App\Models\SysStdPushConfig;
use App\Models\SysStdRefund;
use App\Services\Hub\HubRequestEnum;

/**
 * 标准退单 hub client 操作
 * Class SysStdRefundController
 * @package App\Http\Controllers\Admin
 */
class SysStdRefundClientController extends Controller
{
    public function getProxy($method)
    {
        $pushConfig = (new SysStdPushConfig())->methodMapCache($method);

        return !empty($pushConfig['proxy']) ? $pushConfig['proxy'] : null;
    }

    protected function getRequest($method, $content)
    {
        $proxy = $this->getProxy($method);
        $request = HubClient::hub($proxy)->resolveRequestClass($method);

        return $request->setContent($content);
    }

    /**
     * 退单推送
     *
     * @param $id
     * @return mixed
     *
     * @author linqihai
     * @since 2020/1/6 20:29
     */
    public function push($id)
    {
        $stdRefund = SysStdRefund::findOrFail($id);

        if (1 == $stdRefund['has_good_return']) { // 退货退款
            if (cutoverTrade($stdRefund['tid'], $stdRefund['platform'])) {
                $method = HubRequestEnum::REFUND_RETURN_CREATE_EXTEND;
            } else {
                $method = HubRequestEnum::REFUND_RETURN_CREATE;
            }
            $request = $this->getRequest($method, $stdRefund->toArray());
            $result = HubClient::hub($this->getProxy($method))->execute($request);
        } else { // 仅退款
            $request = $this->getRequest(HubRequestEnum::REFUND_CREATE, $stdRefund->toArray());
            $result = HubClient::hub($this->getProxy(HubRequestEnum::REFUND_CREATE))->execute($request);
        }
        if (!$result['status']) {
            return $this->failed($result['message']);
        }
        return $this->success($result);
    }

    /**
     * 退单推送报文
     *
     * @param $id
     * @return mixed
     *
     * @author linqihai
     * @since 2020/1/6 20:29
     */
    public function pushFormat($id)
    {
        $stdRefund = SysStdRefund::findOrFail($id);
        if (1 == $stdRefund['has_good_return']) {
            if (cutoverTrade($stdRefund['tid'], $stdRefund['platform'])) {
                $method = HubRequestEnum::REFUND_RETURN_CREATE_EXTEND;
            } else {
                $method = HubRequestEnum::REFUND_RETURN_CREATE;
            }
            $request = $this->getRequest($method, $stdRefund->toArray());
        } else {
            $request = $this->getRequest(HubRequestEnum::REFUND_CREATE, $stdRefund->toArray());
        }
        $result = $request->getBody();

        return $this->respond($result);
    }

    /**
     * 退单取消推送
     *
     * @param $id
     * @return mixed
     *
     * @author linqihai
     * @since 2020/1/6 20:29
     */
    public function pushCancel($id)
    {
        $stdRefund = SysStdRefund::findOrFail($id);

        if (1 == $stdRefund['has_good_return']) {
            $request = $this->getRequest(HubRequestEnum::REFUND_RETURN_CANCEL, $stdRefund->toArray());
            $result = HubClient::hub($this->getProxy(HubRequestEnum::REFUND_RETURN_CANCEL))->execute($request);
        } else {
            $request = $this->getRequest(HubRequestEnum::REFUND_CANCEL, $stdRefund->toArray());
            $result = HubClient::hub($this->getProxy(HubRequestEnum::REFUND_CANCEL))->execute($request);
        }
        if (!$result['status']) {
            return $this->failed($result['message']);
        }
        return $this->success($result);
    }

    /**
     * 退单取消推送报文
     *
     * @param $id
     * @return mixed
     *
     * @author linqihai
     * @since 2020/1/6 20:30
     */
    public function pushCancelFormat($id)
    {
        $stdRefund = SysStdRefund::findOrFail($id);
        if (1 == $stdRefund['has_good_return']) {
            $request = $this->getRequest(HubRequestEnum::REFUND_RETURN_CANCEL, $stdRefund->toArray());
        } else {
            $request = $this->getRequest(HubRequestEnum::REFUND_CANCEL, $stdRefund->toArray());
        }
        $result = $request->getBody();

        return $this->respond($result);
    }

    /**
     * 退单运单号变更推送
     *
     * @param $id
     * @return mixed
     *
     * @author linqihai
     * @since 2020/1/6 20:30
     */
    public function pushLogistic($id)
    {
        $stdRefund = SysStdRefund::where(['has_good_return' => 1])->findOrFail($id);

        $request = $this->getRequest(HubRequestEnum::REFUND_RETURN_LOGISTIC_MODIFY, $stdRefund->toArray());
        $result = HubClient::hub($this->getProxy(HubRequestEnum::REFUND_RETURN_LOGISTIC_MODIFY))->execute($request);
        if (!$result['status']) {
            return $this->failed($result['message']);
        }
        return $this->success($result);
    }

    /**
     * 退单运单号变更推送报文
     *
     * @param $id
     * @return mixed
     *
     * @author linqihai
     * @since 2020/1/6 20:30
     */
    public function pushLogisticFormat($id)
    {
        $stdRefund = SysStdRefund::where(['has_good_return' => 1])->findOrFail($id);
        $request = $this->getRequest(HubRequestEnum::REFUND_RETURN_LOGISTIC_MODIFY, $stdRefund->toArray());
        $result = $request->getBody();

        return $this->respond($result);
    }
}
