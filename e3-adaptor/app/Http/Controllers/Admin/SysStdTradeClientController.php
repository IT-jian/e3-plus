<?php

namespace App\Http\Controllers\Admin;

use App\Facades\HubClient;
use App\Models\SysStdPushConfig;
use App\Models\SysStdRefund;
use App\Models\SysStdTrade;
use App\Services\Hub\HubRequestEnum;

/**
 * 标准订单
 * Class SysStdTradeController
 * @package App\Http\Controllers\Admin
 */
class SysStdTradeClientController extends Controller
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

    /**
     * 订单推送
     *
     * @param $id
     * @return mixed
     *
     * @author linqihai
     * @since 2020/1/6 20:29
     */
    public function push($id)
    {
        $stdTrade = SysStdTrade::findOrFail($id);
        $request = $this->getRequest(HubRequestEnum::TRADE_CREATE, $stdTrade->toArray());
        $result = HubClient::hub($this->getProxy(HubRequestEnum::TRADE_CREATE))->execute($request);
        if (!$result['status']) {
            return $this->failed($result['message']);
        }

        return $this->success($result);
    }

    /**
     * 订单推送报文
     *
     * @param $id
     * @return mixed
     *
     * @author linqihai
     * @since 2020/1/6 20:29
     */
    public function pushFormat($id)
    {
        $stdTrade = SysStdTrade::findOrFail($id);
        $request = $this->getRequest(HubRequestEnum::TRADE_CREATE, $stdTrade->toArray());
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
        $stdRefunds = SysStdRefund::where('tid', $id)->get();
        $failCount = 0;
        foreach ($stdRefunds as $stdRefund) {
            $request = $this->getRequest(HubRequestEnum::TRADE_CANCEL, $stdRefund->toArray());
            $result = HubClient::hub($this->getProxy(HubRequestEnum::STEP_TRADE_CANCEL))->execute($request);
            if (!$result['status']) {
                $failCount++;
            }
        }
        if ($failCount) {
            return $this->failed('Part Push Fail!');
        }
        return $this->success([]);
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
        $stdRefund = SysStdRefund::where('tid', $id)->firstOrFail();
        $request = $this->getRequest(HubRequestEnum::TRADE_CANCEL, $stdRefund->toArray());
        $result = $request->getBody();

        return $this->respond($result);
    }

    /**
     * 预售未付尾款-取消推送
     *
     * @param $id
     * @return mixed
     *
     * @author linqihai
     * @since 2020/1/6 20:29
     */
    public function pushStepCancel($id)
    {
        $stdTrade = SysStdTrade::where('tid', $id)->firstOrFail();

        $request = $this->getRequest(HubRequestEnum::STEP_TRADE_CANCEL, $stdTrade->toArray());
        $result = HubClient::hub($this->getProxy(HubRequestEnum::STEP_TRADE_CANCEL))->execute($request);
        if (!$result['status']) {
            return $this->failed($result['message']);
        }

        return $this->success($result);
    }

    /**
     * 预售未付尾款-推送报文
     *
     * @param $id
     * @return mixed
     *
     * @author linqihai
     * @since 2020/1/6 20:30
     */
    public function pushStepCancelFormat($id)
    {
        $stdTrade = SysStdTrade::where('tid', $id)->firstOrFail();
        $request = $this->getRequest(HubRequestEnum::STEP_TRADE_CANCEL, $stdTrade->toArray());
        $result = $request->getBody();

        return $this->respond($result);
    }

    /**
     * 预售支付尾款-支付完成推送
     *
     * @param $id
     * @return mixed
     *
     * @since 2020/4/26 20:01
     */
    public function pushStepPaid($id)
    {
        $stdTrade = SysStdTrade::where('tid', $id)->firstOrFail();

        $request = $this->getRequest(HubRequestEnum::STEP_TRADE_PAID, $stdTrade->toArray());
        $result = HubClient::hub($this->getProxy(HubRequestEnum::STEP_TRADE_PAID))->execute($request);
        if (!$result['status']) {
            return $this->failed($result['message']);
        }

        return $this->success($result);
    }

    /**
     * 预售支付尾款-推送报文
     *
     * @param $id
     * @return mixed
     *
     * @author linqihai
     * @since 2020/4/26 19:58
     */
    public function pushStepPaidFormat($id)
    {
        $stdTrade = SysStdTrade::where('tid', $id)->firstOrFail();
        $request = $this->getRequest(HubRequestEnum::STEP_TRADE_PAID, $stdTrade->toArray());
        $result = $request->getBody();

        return $this->respond($result);
    }

    /**
     * 收货地址变更
     *
     * @param $id
     * @return mixed
     *
     * @author linqihai
     * @since 2020/1/6 20:30
     */
    public function pushAddressModify($id)
    {
        $stdTrade = SysStdTrade::findOrFail($id);
        $request = $this->getRequest(HubRequestEnum::TRADE_ADDRESS_MODIFY, $stdTrade->toArray());
        $result = HubClient::hub($this->getProxy(HubRequestEnum::TRADE_ADDRESS_MODIFY))->execute($request);
        if (!$result['status']) {
            return $this->failed($result['message']);
        }

        return $this->success($result);
    }

    /**
     * 收货地址变更推送报文
     *
     * @param $id
     * @return mixed
     *
     * @author linqihai
     * @since 2020/1/6 20:30
     */
    public function pushAddressModifyFormat($id)
    {
        $stdTrade = SysStdTrade::findOrFail($id);
        $request = $this->getRequest(HubRequestEnum::TRADE_ADDRESS_MODIFY, $stdTrade->toArray());
        $result = $request->getBody();

        return $this->respond($result);
    }
}
