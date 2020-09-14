<?php


namespace App\Services\Hub\Adidas\Request\Transformer;


use App\Models\SysStdExchangeItem;
use App\Models\SysStdTrade;
use Illuminate\Support\Carbon;
use Spatie\ArrayToXml\ArrayToXml;

class ExchangeCreateTransformer extends BaseTransformer
{
    public function format($stdExchange)
    {
        // 判断是否加强版报文
        if (cutoverTrade($stdExchange['tid'], $stdExchange['platform'])) {
            // 查询订单
            $where = [
                'tid' => $stdExchange['tid'],
                'platform' => $stdExchange['platform']
            ];
            $trade = SysStdTrade::where($where)->firstOrFail();

            return (new ExchangeCreateExtendTransformer())->format($stdExchange, $trade);
        }

        $rootElement = $this->getRootElement();
        $content = $this->getContent($stdExchange);

        return ArrayToXml::convert($content, $rootElement, false, 'UTF-8');
    }

    protected function getRootElement()
    {
        return [
            'rootElementName' => 'createreturnorder'
        ];
    }

    protected function getContent($exchange)
    {
        $where = [
            'dispute_id' => $exchange['dispute_id'],
            'platform' => $exchange['platform']
        ];
        // 查询明细
        $exchangeItems = SysStdExchangeItem::where($where)->get();
        $orders = [];
        // 组织 Order 数据
        $orders[] = $this->formatReturnOrder($exchange, $exchangeItems);
        $orders[] = $this->formatExchangeOrder($exchange, $exchangeItems);

        $content = [
            'Order' => $orders,
        ];

        return $content;
    }

    /**
     * 退货 order 格式
     *
     * @param $exchange
     * @param $items
     * @return array
     *
     * @author linqihai
     * @since 2019/12/24 14:45
     */
    private function formatReturnOrder($exchange, $items)
    {
        $orderLines = [];
        $defaultRowIndex = 0;
        $price = 0;
        foreach ($items as $item) {
            $defaultRowIndex++;
            // $item['row_index'] = $defaultRowIndex;
            $orderLine = [
                '_attributes' => [
                    'PrimeLineNo'      => $item['row_index'],
                    'Quantity'         => $item['num'],
                    'ReturnReasonCode' => $this->exchangeReasonCodeMap($item['reason'], $exchange['platform']),
                    'ReturnReasonText' => $item['reason'] ?? '',
                    'CustomerLinePONo' => !empty($item['oid']) ? $item['oid'] : $item['row_index'],
                ],
                'Extn'        => [
                    '_attributes' => [// $item['outer_iid']
                        'ExtnCustItemID'       => $item['bought_outer_iid'], // $item['bought_sku']
                    ],
                ],
                'Item'        => [
                    '_attributes' => [
                        'ItemID' => $this->mapItemId($item['bought_outer_sku_id']),
                    ],
                ],
            ];

            $orderLines[] = $orderLine;
            $price += $item['price'];
        }

        $exchange['price'] = $price;

        $content = [
            '_attributes' => [
                'EnterpriseCode' => $exchange['shop_code'],
                'DocumentType'   => '0001',
                'OrderNo'        => $this->generatorOrderNo($exchange['tid'], $exchange['platform']),
                'OrderDate'      => Carbon::createFromTimeString($exchange['created'])->toIso8601String(),
                'EntryType'      => $this->entryTypeMap($exchange['platform']),
                'CustomerPONo'   => '',
            ],
            'Extn'        => [
                '_attributes' => [
                    'ExtnReturnOrderNo' => $this->generatorRefundNo($exchange['dispute_id'], $exchange['platform']),
                    'ExtnStatus'        => $exchange['status'],
                    'ExtnCustPONo'         => $exchange['tid'],
                    'ExtnOrderNo'         => $this->generatorOrderNo($exchange['tid'], $exchange['platform']),
                ],
            ],
            'OrderLines'  => [
                'OrderLine' => $orderLines
            ],
        ];

        return $content;
    }

    /**
     * 换货 order 格式
     * @param $exchange
     * @param $items
     * @return array
     *
     * @author linqihai
     * @since 2019/12/24 14:45
     */
    private function formatExchangeOrder($exchange, $items)
    {
        $orderLines = [];
        $defaultRowIndex = 0;
        foreach ($items as $item) {
            $itemId = $this->mapItemId($item['exchange_outer_sku_id']);
            $itemIdArr = explode('_', $itemId, 2);
            $defaultRowIndex++;
            // $item['row_index'] = $defaultRowIndex;
            $orderLine = [
                '_attributes' => [
                    'LineType'         => 'inline',
                    'OrderedQty'       => $item['num'],
                    'PrimeLineNo'      => $item['row_index'],
                    'CustomerLinePONo' => !empty($item['oid']) ? $item['oid'] : $item['row_index'],
                ],
                'Extn'        => [
                    '_attributes' => [
                        'ExtnCustItemID'    => $item['exchange_outer_iid'],
                        'ExtnArticleNumber' => $itemIdArr[0] ?? "",
                        'ExtnLocalSizeCode' => $item['exchange_size'],
                        'ExtnSizeCode'      => $itemIdArr[1] ?? "",
                        'ExtnColor'         => $item['exchange_color'],
                        'ExtnDivisionCode'  => "",
                        'ExtnEAN'           => $item['exchange_outer_sku_id'],
                        'ExtnModelNo'       => "",
                        'ExtnDisputeId'     => $exchange['dispute_id'],
                    ],
                ],
                'Item'        => [
                    '_attributes' => [
                        'ItemDesc'      => $item['exchange_title'],
                        'ItemID'        => $itemId,
                        'ItemShortDesc' => '',
                        'ProductClass'  => 'NEW',
                        'ReturnItemID'  => $item['bought_outer_iid'],
                        'UPCCode'       => '',
                        'UnitOfMeasure' => 'PIECE',
                    ],
                ],
                'LineCharges' => [
                    'LineCharge' => [
                        '_attributes' => [
                            'ChargeCategory' => '',
                            'ChargeName'     => '',
                            'ChargePerLine'  => '',
                            'ChargePerUnit'  => '',
                            'Reference'      => '',
                        ],
                        'Extn'        => [
                            '_attributes' => [
                                'ExtnCampaignId' => "",
                            ],
                        ],
                    ],
                ],
                'LineTaxes'   => [
                    'LineTax' => [
                        '_attributes' => [
                            'ChargeCategory' => '',
                            'ChargeName'     => '',
                            'Reference1'     => '',
                            'Tax'            => '',
                            'TaxName'        => '',
                            'TaxPercentage'  => '',
                        ],
                    ],
                ],
                'LinePriceInfo'    => [
                    '_attributes' => [
                        'ListPrice' => $item['price'],
                        'UnitPrice' => $item['price'],
                    ],
                ],
                'PersonInfoShipTo' => $this->shippingInfo($exchange),
                // 'PersonInfoBillTo' => $this->shippingInfo($exchange),
            ];

            $orderLines[] = $orderLine;
        }

        $content = [
            '_attributes'      => [
                'EnteredBy'    => 'Marketplace',
                'EntryType'    => $this->entryTypeMap($exchange['platform']),
                'OrderPurpose' => 'EXCHANGE',
                'OrderNo'      => $this->generatorExchangeNo($exchange['dispute_id'], $exchange['platform']),
                'CustomerPONo' => $exchange['dispute_id'],
                'OrderDate'    => Carbon::createFromTimeString($exchange['created'])->toIso8601String(),
            ],
            'Extn'        => [
                '_attributes' => [
                    'ExtnCustPONo'          => $exchange['tid'],
                    'ExtnOrderNo'           => $this->generatorOrderNo($exchange['tid'], $exchange['platform']),
                    'ExtnStatus'            => $exchange['status'],
                ],
            ],
            'HeaderCharges'    => [],
            'PersonInfoBillTo' => $this->shippingInfo($exchange),
            'HeaderTaxes'      => [],
            'OrderLines'       => [
                'OrderLine' => $orderLines,
            ],
            'PersonInfoShipTo' => $this->shippingInfo($exchange),
            'PaymentMethods'   => [],
            'PriceInfo'        => [],
        ];

        // 系统切换时，加强版报文
        return $content;
    }

    public function shippingInfo($exchange)
    {
        $region = $this->parseAddressDetail($exchange['buyer_address']);
        $address1 = $region['address'];
        $address2 = $address3 = $address4 = '';
        if ('taobao' == $exchange['platform'] && mb_strlen($address1, 'UTF-8') > 50) {
            $addressArr = mb_str_split($address1, 50, 'UTF-8');
            $address1 = $addressArr[0] ?? '';
            $address2 = $addressArr[1] ?? '';
            $address3 = $addressArr[2] ?? '';
            $address4 = $addressArr[3] ?? '';
        }
        return [
            '_attributes' => [
                'State'        => $this->stateMap($region['province']),
                'City'         => $region['city'],
                'AddressLine1' => $address1,
                'AddressLine2' => $address2,
                'AddressLine3' => $address3,
                'AddressLine4' => $address4,
                'AddressLine5' => $region['district'],
                'AddressLine6' => $region['province'],
                'Country'      => 'CN',
                'EMailID'      => '',
                'FirstName'    => $exchange['buyer_name'],
                'MiddleName'   => '',
                'LastName'     => '',
                'ZipCode'      => '',
                'DayPhone'     => $exchange['buyer_phone'] ?? '',
            ]
        ];
    }

    /**
     * 淘宝换货地址解析
     * 符号【^^^】 分隔省市区
     *
     * @param $detailAddress
     * @return array
     */
    public function parseAddressDetail($detailAddress)
    {
        // 不包含分隔符
        if (false === strpos($detailAddress, '^^^')) {
            return [
                'province' => '',
                'city'     => '',
                'district' => '',
                'address'  => $detailAddress,
            ];
        }
        // 淘宝换货地址解析
        $addrArr = explode('^^^',$detailAddress);
        $province = $addrArr[0];
        $city = $addrArr[1];
        $dis = $addrArr[2];
        $addr = $addrArr[3];
        //地址特殊的省份,省下面没有市直接是县
        if(in_array($province, ['海南省']) && $city == ' '){
            $city = $dis;
            $dis = '';
        }

        if (empty(trim($city))) {
            $city = $dis;
        }

        return [
            'province' => $province,
            'city'     => $city,
            'district' => $dis,
            'address'   => $addr,
        ];
    }
}
