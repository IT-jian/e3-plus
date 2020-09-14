<?php


namespace App\Services\Hub\Adidas\Request\Transformer\Traits;


trait TradeCommonPartTrait
{
    public function formatPaymentMethods($trade)
    {
        return [
            'PaymentMethod' => [
                '_attributes'    => [
                    'CustomerPONo'      => $trade['tid'],
                    'PaymentType'       => 'PartnerPay', // 默认值
                    'PaymentReference2' => $trade['shop_code'],
                    'PaymentReference1' => $trade['shop_code'],
                    // 'PaymentReference9' => $trade['tid'],
                ],
                'PaymentDetails' => [
                    '_attributes' => [
                        // 'RequestId'       => $trade['tid'], // 如果是加强版报文，才需要
                        'RequestAmount'   => ('step' == $trade['type'] ? $trade['step_paid_fee'] : $trade['payment']),
                        'AuthorizationID' => !empty($trade['pay_no']) ? $trade['pay_no'] : $trade['tid'],
                        'AuthAVS'         => !empty($trade['pay_no']) ? $trade['pay_no'] : $trade['tid'],
                    ],
                ],
            ],
        ];
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

    public function linePriceInfo($item)
    {
        return [
            '_attributes' => [
                'PricingUOM'    => 'PIECE',
                'IsPriceLocked' => 'Y',
                'UnitPrice'     => $item['price'],
                'ListPrice'     => $item['price'],
                'RetailPrice'   => $item['price'],
                'TaxableFlag'   => 'Y',
            ],
        ];
    }
}
