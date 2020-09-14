<?php


namespace App\Services\Hub\Adidas\Request\Transformer;


use App\Models\AdidasItem;
use App\Models\SysStdReasonMap;
use Illuminate\Support\Str;

class BaseTransformer
{
    /**
     * EntryType 对应的平台类型编码
     *
     * @param $platform
     * @return mixed|string
     *
     * @author linqihai
     * @since 2020/4/20 17:55
     */
    public function entryTypeMap($platform)
    {
        $map = [
            'taobao'   => 'TMALL',
            'jingdong' => 'JD',
        ];

        return $map[$platform] ?? '';
    }

    public function generatorNoPrefix($platform)
    {
        $map = [
            'taobao'   => 'TM',
            'jingdong' => 'JD',
        ];

        return $map[$platform] ?? '';
    }

    /**
     * 中文省映射的英文缩写
     *
     * 获取省份前两个字来匹配
     *
     * @param $state
     * @return mixed|string
     *
     * @author linqihai
     * @since 2020/4/20 17:55
     */
    public function stateMap($state)
    {
        $formatState = substr($state, 0, 6);
        $stateMap = [
            '安徽'        => 'AH',
            '北京'        => 'BJ',
            '重庆'        => 'CQ',
            '福建'        => 'FJ',
            '广东'        => 'GD',
            '甘肃'        => 'GS',
            '广西'        => 'GX',
            '广西壮族自治区'   => 'GX',
            '贵州'        => 'GZ',
            '河南'        => 'HA',
            '湖北'        => 'HB',
            '河北'        => 'HE',
            '海南'        => 'HI',
            '黑龙'        => 'HL',
            '黑龙江'       => 'HL',
            '湖南'        => 'HN',
            '吉林'        => 'JL',
            '江苏'        => 'JS',
            '江西'        => 'JX',
            '辽宁'        => 'LN',
            '内蒙'        => 'NM',
            '内蒙古'       => 'NM',
            '内蒙古自治区'    => 'NM',
            '宁夏'        => 'NX',
            '宁夏回族自治区'   => 'NX',
            '青海'        => 'QH',
            '四川'        => 'SC',
            '山东'        => 'SD',
            '上海'        => 'SH',
            '陕西'        => 'SN',
            '山西'        => 'SX',
            '天津'        => 'TJ',
            '新疆'        => 'XJ',
            '新疆维吾尔族自治区' => 'XJ',
            '西藏'        => 'XZ',
            '云南'        => 'YN',
            '浙江'        => 'ZJ',
            '澳门'        => 'MO',
            '澳门特别行政区'   => 'MO',
            '香港'        => 'HK',
            '香港特别行政区'   => 'HK',
            '台湾'        => 'TW',
        ];

        return $stateMap[$formatState] ?? '';
    }

    /**
     * 退单原因 map
     *
     * @param $code
     * @param $platform
     * @return mixed|string
     *
     * @author linqihai
     * @since 2020/1/6 15:37
     */
    public function refundReasonCodeMap($code, $platform = 'taobao')
    {
        $type = 'return';
        $map = app(SysStdReasonMap::class)->reasonMapCache($platform, $type);

        return $map[$code] ?? '';
    }

    /**
     * 换货单原因map
     *
     * @param $code
     * @param string $platform
     * @return string
     *
     * @author linqihai
     * @since 2020/2/24 20:34
     */
    public function exchangeReasonCodeMap($code, $platform = 'taobao')
    {
        $type = 'exchange';
        $map = app(SysStdReasonMap::class)->reasonMapCache($platform, $type);

        return $map[$code] ?? '';
    }

    /**
     * 订单取消原因
     * @param $code
     * @param string $platform
     * @return string
     *
     * @author linqihai
     * @since 2020/2/24 20:34
     */
    public function tradeCancelReasonCodeMap($code, $platform = 'taobao')
    {
        $type = 'trade_cancel';
        $map = app(SysStdReasonMap::class)->reasonMapCache($platform, $type);

        return $map[$code] ?? 'A11';
    }

    /**
     * 生成订单号
     *
     * @param $tid
     * @param $platform
     * @return string
     *
     * @author linqihai
     * @since 2020/4/20 17:56
     */
    public function generatorOrderNo($tid, $platform)
    {
        $first = substr($tid, 0, 1);
        $last = substr($tid, -1, 1);
        $prefix = 'taobao' == $platform ? 'T' : 'J';

        return $prefix . $first . num16to32($tid) . $last;
    }

    /**
     * 生成退单号
     *
     * @param $refundId
     * @param $platform
     * @return string
     *
     * @author linqihai
     * @since 2020/4/20 17:56
     */
    public function generatorRefundNo($refundId, $platform)
    {
        $prefix = 'taobao' == $platform ? 'T' : 'J';

        return $prefix . 'R' . num16to32($refundId);
    }

    /**
     * 生成换货单号
     *
     * @param $disputeId
     * @param $platform
     * @return string
     *
     * @author linqihai
     * @since 2020/4/20 17:56
     */
    public function generatorExchangeNo($disputeId, $platform)
    {
        return $this->generatorNoPrefix($platform) . 'X' . num16to32($disputeId);
    }

    /**
     * 转化 ItemId mapping
     *  Omnihub上线后使用新的商家编码方案（outer_sku_id），但是平台维护的商家编码有新的也有旧了
     *  为了能让Omnihub统一识别，则需要Adapter通过一个Mapping表匹配识别并将新匹配到的商家编码在订单创建时推送给Omnihub
     *
     * @param string $outerSkuId 商家外部编码
     * @return string
     *
     * @author linqihai
     * @since 2020/3/25 15:45
     */
    public function mapItemId($outerSkuId)
    {
        return app(AdidasItem::class)->mapItemId($outerSkuId);
    }

    public function giftFlag($item)
    {
        $flag = '';
        if (Str::contains($item['outer_iid'], ['GWP'])) {
            $flag = 'Y';
        }

        return $flag;
    }
}
