<?php


namespace App\Services\HubApi\Adidas\Jingdong\Api;


use App\Facades\JosClient;
use App\Models\JingdongRefund;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Jingdong\Client\Jos\Request\AscProcessOfflineChangeRequest;

/**
 * 01-发货回写 -- 换货单
 *
 * Class ExchangeOfflineSendHubApi
 * @package App\Services\HubApi\Adidas\Jingdong\Api
 */
class ExchangeOfflineSendHubApi extends BaseApi
{
    protected $notNullFields = ['deal_type', 'refund_id', 'deal_code', 'shipping_sn', 'shipping_code', 'shop_code', 'refund_ware_type'];

    public function proxy()
    {
        if (2 == $this->data['deal_type']){ // 换货单回写
            // 查询京东服务单
            $jingdongRefund = JingdongRefund::where('service_id', $this->data['refund_id'])->firstOrFail(['vender_id', 'sys_version']);
            // 获取 shipWay
            $shipWay = $this->getShipWay($this->data['shipping_code']);

            $request = new AscProcessOfflineChangeRequest();
            $request->setBuId($jingdongRefund['vender_id']);
            $request->setServiceId($this->data['refund_id']);
            $request->setOrderId($this->data['deal_code']);
            $request->setSysVersion($jingdongRefund['sys_version']);
            $request->setOpFlag(1);
            $request->setShipWayId($shipWay['id']);
            $request->setShipWayName($shipWay['name']);
            $request->setExpressCode($this->data['shipping_sn']);
            $wareType = empty($this->data['refund_ware_type']) ? '10' : $this->data['refund_ware_type'];
            $request->setWareType($wareType);

            $result = JosClient::shop($this->data['shop_code'])->execute($request);

            return $this->responseSimple($result);
        }

        return $this->fail([], 'unknown deal_type');

    }

    public function isSuccess($response)
    {
        return data_get($response, 'jingdong_asc_process_offline_change_responce.result.success', false);
    }

    /**
     * 京东承运商信息
     *
     * @param $shippingCode
     * @return array|mixed
     */
    public function getShipWay($shippingCode)
    {
        $shippingCode = strtolower($shippingCode);
        $map = [
            'jingdong' => ['id' => 10, 'name' => '京东快递'],
            'yuantong' => ['id' => 20, 'name' => '圆通快递'],
            'sf'       => ['id' => 30, 'name' => '顺丰快递'],
            'post'     => ['id' => 40, 'name' => '邮局EMS'],
            'shentong' => ['id' => 50, 'name' => '申通快递'],
            'ydkd'     => ['id' => 70, 'name' => '韵达'],
            'yzxf'     => ['id' => 80, 'name' => '邮政信封-邮政快递包裹'],
            'ttkd'     => ['id' => 90, 'name' => '天天快递'],
            'yskd'     => ['id' => 1747, 'name' => '优速快递'],
            'yssy'     => ['id' => 100, 'name' => '优速速运'],
            'kdys'     => ['id' => 60, 'name' => '快递运输'],
            'ztkd'     => ['id' => 110, 'name' => '中通快递'],
            'zjs'      => ['id' => 1409, 'name' => '宅急送'],
            'bskd'     => ['id' => 1748, 'name' => '百世快递'],
            'qfkd'     => ['id' => 2016, 'name' => '全峰快递'],
            'kjkd'     => ['id' => 2094, 'name' => '快捷快递'],
            'lbkd'     => ['id' => 2096, 'name' => '联邦快递'],
            'qykd'     => ['id' => 2100, 'name' => '全一快递'],
            'sekd'     => ['id' => 2105, 'name' => '速尔快递'],
            'yzkdbg'   => ['id' => 2170, 'name' => '邮政快递包裹'],
            'gtkd'     => ['id' => 2465, 'name' => '国通快递'],
            'kqkd'     => ['id' => 2466, 'name' => '汇强快递'],
            'zykd'     => ['id' => 3044, 'name' => '增益快递'],
            'dbkd'     => ['id' => 3046, 'name' => '德邦快递'],
            'other'    => ['id' => -10, 'name' => '其他快递'],
        ];

        return $map[$shippingCode] ?? ['id' => -10, 'name' => '其他快递'];
    }
}
