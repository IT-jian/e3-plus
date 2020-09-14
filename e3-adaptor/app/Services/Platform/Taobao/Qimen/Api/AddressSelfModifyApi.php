<?php


namespace App\Services\Platform\Taobao\Qimen\Api;

use App\Facades\HubClient;
use App\Models\SysStdTrade;
use App\Services\Platform\Taobao\Qimen\ApiContracts;
use App\Services\Platform\Taobao\Qimen\Exceptions\ServerSideException;
use App\Services\Platform\Taobao\Qimen\Jobs\DelayModifyAddressJob;
use Exception;

class AddressSelfModifyApi extends BaseQimenApi implements ApiContracts
{

    /**
     * 执行请求
     *
     * @param $content
     * {
     * "sellerNick": "anta安踏吉元科创专卖店",
     * "buyerNick": "tb724986910",
     * "originalAddress": { // 原地址
     * "area": "诸暨市",
     * "country": "",
     * "addressDetail": "暨阳街道孙陈菜场乡香老面馒头店.",
     * "province": "浙江省",
     * "town": "暨阳街道",
     * "city": "绍兴市",
     * "phone": "13777300691",
     * "name": "陈川霞",
     * "postCode": "000000"
     * },
     * "bizOrderId": "99 04392450971180529", // 交易订单ID
     * "modifiedAddress": { // 要修改的地址
     * "area": "都昌县",
     * "country": " ",
     * "addressDetail": "南峰镇街上",
     * "province": "江西省",
     * "town": "南峰镇",
     * "city": "九江市",
     * "phone": "15979363884",
     * "name": "冯巧文",
     * "postCode": "000000"
     * }
     * }
     * @return array
     * @throws Exception
     * @author linqihai
     * @since 2020/3/23 11:45
     */
    public function execute($content)
    {
        $tid = $content['bizOrderId'];
        $stdTrade = SysStdTrade::where('tid', $tid)->first();
        if (empty($stdTrade)) {
            $this->delayJob($content);

            return $this->success();
        }
        // 是否校验原地址
        $modifiedAddress = $content['modifiedAddress'];
        $newAddress = [
            'receiver_name'     => $modifiedAddress['name'] ?? '',
            'receiver_state'    => $modifiedAddress['province'] ?? '',
            'receiver_city'     => $modifiedAddress['city'] ?? '',
            'receiver_district' => $modifiedAddress['area'] ?? '',
            'receiver_town'     => $modifiedAddress['town'] ?? '',
            'receiver_address'  => $modifiedAddress['addressDetail'] ?? '',
            'receiver_zip'      => $modifiedAddress['postCode'] ?? '',
            'receiver_mobile'    => $modifiedAddress['phone'] ?? '',
        ];

        try {
            $stdTrade->fill($newAddress);
            $result = HubClient::tradeAddressModify($stdTrade->toArray());
            if (!$result['status'] && !$this->shouldResponseSuccess($result)) {
                throw new Exception($result['message'] ?? '修改地址失败');
            }
            $result['status'] = true;
            // 请求返回成功，则修改本地地址
            if ($result['status']) {
                $stdTrade->save();
            } else { // 否则视为，请求修改地址失败，需要重试
                $this->delayJob($content);
            }
        } catch (Exception $e) {
            $result = [
                'status'  => false,
                'message' => $e->getMessage(),
            ];
        }

        return $result;
    }

    /**
     * 根据响应的错误码，判断是否
     *
     * @param $result
     * @return bool
     *
     * @author linqihai
     * @since 2020/3/27 11:50
     */
    public function shouldResponseSuccess($result)
    {
        if (!empty($result['message'])) {
            return in_array($result['message'], ['YFS:Invalid Order']);
        }

        return false;
    }

    /**
     * 延后执行
     *
     * @param $content
     * @return bool|mixed
     */
    public function delayJob($content)
    {
        // 来自 job 不再发起
        if (isset($content['from_adaptor_job']) && !empty($content['from_adaptor_job'])) {
            return true;
        }
        return dispatch((new DelayModifyAddressJob($content))->delay(60));
    }
}