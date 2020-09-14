<?php


namespace App\Services\Adaptor\Jingdong\Downloader;


use App\Facades\Adaptor;
use App\Facades\JosClient;
use App\Models\SysStdTrade;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Contracts\DownloaderContract;
use App\Services\Adaptor\Jingdong\Jobs\JingdongExchangeTransferJob;
use App\Services\Adaptor\Jingdong\Jobs\JingdongRefundTransferJob;
use App\Services\Adaptor\Jingdong\Repository\JingdongRefundRepository;
use App\Services\Platform\Jingdong\Client\Jos\Request\AscFreightViewRequest;
use App\Services\Platform\Jingdong\Client\Jos\Request\AscQueryViewRequest;
use App\Services\Platform\Jingdong\Client\Jos\Request\AscServiceAndRefundViewRequest;

class RefundDownloader implements DownloaderContract
{
    const CUSTOMER_EXPECT_RETURN = 10; // 退货
    const CUSTOMER_EXPECT_EXCHANGE = 20; // 换货
    const CUSTOMER_EXPECT_FIX = 30; // 维修
    const CUSTOMER_EXPECT_BUFA = 80; // 补发
    const CUSTOMER_EXPECT_FRESH = 90; // 优鲜赔

    protected $repository;
    protected $shop;

    public function __construct(JingdongRefundRepository $repository)
    {
        $this->repository = $repository;
    }

    public function download($refund)
    {
        $this->shop = $refund['shop'];

        $refundDetail = $this->getDetail($refund);
        // 不比较了，快递更新 服务单不会更新内容，心塞
        // 查询是否有版本号，比较版本号
        /*if (isset($refundDetail['sysVersion'])) {
            $exist = $this->repository->getRow(['service_id' => $refund['serviceId']], ['sys_version']);
            if ($exist && $refundDetail['sysVersion'] == $exist->sys_version) {
                return true;
            }
        }*/

        $refunds = $this->saveRefundDetails($refundDetail);
        foreach ($refunds as $refund) {
            $refund['shop_code'] = $this->shop['code'];
            if (self::CUSTOMER_EXPECT_RETURN == $refund['customer_expect']) {
                dispatch(new JingdongRefundTransferJob($refund)); // 格式化订单
            } else if (self::CUSTOMER_EXPECT_EXCHANGE == $refund['customer_expect']) {
                dispatch(new JingdongExchangeTransferJob($refund)); // 格式化退单
            }
        }

        return $refunds;
    }

    public function saveRefundDetails($refund)
    {
        // 如果订单未下载，则优先下载
        $exist = SysStdTrade::where('platform', 'jingdong')->where('tid', $refund['orderId'])->exists();
        if (!$exist) {
            $params = ['order_id' => $refund['orderId'], 'shop_code' => $this->shop['code']];
            Adaptor::platform('jingdong')->download('tradeApi', $params);
            Adaptor::platform('jingdong')->transfer(AdaptorTypeEnum::TRADE, $params);
        }
        $formatData = [];
        $formatData[] = $this->format($refund);
        $updateFields = ['service_status', 'customer_expect', 'apply_time', 'update_date', 'origin_content', 'sys_version', 'origin_modified', 'sync_status'];
        if ($formatData) {
            $this->repository->insertMulti($formatData, $updateFields);
        }

        return $formatData;
    }

    public function format($refund, $applyInfo = [])
    {
        return [
            'service_id'      => $refund['serviceId'],
            'vender_id'       => $this->shop['seller_nick'],
            'order_id'        => $refund['orderId'],
            'service_status'  => $refund['serviceStatus'],
            'customer_expect' => $refund['customerExpect'],
            'change_sku'      => data_get($refund, 'wareChangeWithApplyDTO.changeWareSku', 0),
            'apply_time'      => $refund['applyTime'],
            'update_date'     => $refund['updateDate'],
            'origin_content'  => json_encode($refund),
            'origin_created'  => time(),
            'origin_modified' => time(),
            'sync_status'     => 0,
            'sys_version'     => $refund['sysVersion'],
        ];
    }

    public function getDetail($refund)
    {
        $requests = [];
        // 订单详情
        $request = new AscQueryViewRequest();
        $request->setServiceId($refund['serviceId']);
        $request->setOrderId($refund['orderId']);
        $request->setBuId($this->shop['seller_nick']);
        $request->setOperatePin('adaptor');
        $requests['detail'] = $request->setOperateNick('adaptor');

        // 退款
        $request = new AscServiceAndRefundViewRequest();
        $requests['refund'] = $request->setOrderId($refund['orderId']);

        // 运单信息
        $request = new AscFreightViewRequest();
        $request->setServiceId($refund['serviceId']);
        $request->setOrderId($refund['orderId']);
        $request->setBuId($this->shop['seller_nick']);
        $request->setOperatePin('adaptor');
        $requests['ship'] = $request->setOperateNick('adaptor');

        $response = JosClient::shop($this->shop['code'])->execute($requests, $this->shop['access_token']);

        $detail = data_get($response['detail'], 'jingdong_asc_query_view_responce.result.data', []);
        // 保存到主信息中
        $detail['freightMessage'] = data_get($response['ship'], 'jingdong_asc_freight_view_responce.result.data', []);
        $serviceAndRefunds = data_get($response['refund'], 'jingdong_asc_serviceAndRefund_view_responce.pageResult.data', []);
        if ($serviceAndRefunds) {
            $targetRefund = [];
            if (1 == count($serviceAndRefunds)) {
                $targetRefund = current($serviceAndRefunds);
            } else {
                foreach ($serviceAndRefunds as $serviceAndRefund) {
                    if ($refund['serviceId'] == data_get($serviceAndRefund, 'sameOrderServiceBill.serviceId')) {
                        $targetRefund = $serviceAndRefund;
                        break;
                    }
                }
            }
            if (!empty($targetRefund)) {
                $detail['applyReason'] = data_get($targetRefund, 'sameOrderServiceBill.applyReason', '七天无理由');
            }
            // 退款金额
            $detail['refoundAmount'] = 0;
            $serviceBillDetail = data_get($detail, 'serviceBillDetailList', []);
            foreach ($serviceBillDetail as $bill) {
                $detail['refoundAmount'] += $bill['actualPayPrice'];
            }
        }

        return $detail;
    }
}
