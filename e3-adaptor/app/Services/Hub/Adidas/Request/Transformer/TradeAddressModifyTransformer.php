<?php


namespace App\Services\Hub\Adidas\Request\Transformer;


use App\Models\SysStdTradeItem;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * 组织订单地址更新同步 格式
 * @todo 数据格式
 * Class TradeAddressModifyTransformer
 * @package App\Services\Hub\Adidas\Request\Transformer
 *
 * @author linqihai
 * @since 2020/1/6 15:10
 */
class TradeAddressModifyTransformer extends BaseTransformer
{
    public function format($stdTrade)
    {
        $rootElement = $this->getRootElement($stdTrade);
        $content = $this->getContent($stdTrade);

        return ArrayToXml::convert($content, $rootElement, false, 'UTF-8');
    }

    private function getRootElement($trade)
    {
        return [
            'rootElementName' => 'Order',
            '_attributes'     => [
                'Action'         => 'MODIFY',
                'EnterpriseCode' => $trade['shop_code'],
                'DocumentType'   => '0001',
                'OrderNo'        => $this->generatorOrderNo($trade['tid'], $trade['platform']),
            ],
        ];
    }

    private function getContent($trade)
    {
        $where = [
            'tid'      => $trade['tid'],
            'platform' => $trade['platform'],
        ];
        $orderLines = [];
        // 查询明细
        $tradeItems = SysStdTradeItem::where($where)->get();
        $index = 0;
        $shippingInfo = $this->shippingInfo($trade);
        foreach ($tradeItems as $tradeItem) {
            $index++;
            $orderLines[] = [
                '_attributes'      => [
                    'PrimeLineNo' => $index,
                    'SubLineNo'   => 1,
                ],
                'PersonInfoShipTo' => $shippingInfo,
            ];
        }
        $content = [
            'PersonInfoShipTo' => $shippingInfo,
            'PersonInfoBillTo' => $shippingInfo,
            'OrderLines'       => [
                'OrderLine' => $orderLines,
            ],
        ];

        return $content;
    }

    /**
     * 快递信息
     *
     * @param $trade
     * @return array
     *
     * @author linqihai
     * @since 2019/12/22 18:26
     */
    public function shippingInfo($trade)
    {
        $address1 = $trade['receiver_address'];
        $address2 = $address3 = $address4 = '';
        if ('taobao' == $trade['platform'] && mb_strlen($address1, 'UTF-8') > 50) {
            $addressArr = mb_str_split($address1, 50, 'UTF-8');
            $address1 = $addressArr[0] ?? '';
            $address2 = $addressArr[1] ?? '';
            $address3 = $addressArr[2] ?? '';
            $address4 = $addressArr[3] ?? '';
        }

        return [
            '_attributes' => [
                'State'        => $this->stateMap($trade['receiver_state']),
                'City'         => $trade['receiver_city'],
                'AddressLine1' => $address1,
                'AddressLine2' => $address2,
                'AddressLine3' => $address3,
                'AddressLine4' => $address4,
                'AddressLine5' => $trade['receiver_district'],
                'AddressLine6' => $trade['receiver_state'],
                'Country'      => 'CN',
                'EMailID'      => $trade['buyer_email'] ?? '',
                'FirstName'    => $trade['receiver_name'],
                'MiddleName'   => '',
                'LastName'     => '',
                'ZipCode'      => $trade['receiver_zip'] ?? '',
                'DayPhone'     => $trade['receiver_mobile'] ? $trade['receiver_mobile'] : $trade['receiver_phone'],
            ]
        ];
    }
}
