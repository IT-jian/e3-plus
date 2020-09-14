<?php


class AdidasApiTest extends TestCase
{

    public function testTradeOfflineSend()
    {
        $this->withoutMiddleware();
        \Laravel\Passport\Client::find();
        $body = [
            'method'        => 'e3plus.oms.logistics.offline.send',
            'timestamp'     => time(),
            'shop_code'     => 'EMC1',
            'deal_code'     => '714462209340192864',
            'shipping_code' => 'SF',
            'shipping_sn'   => '288803971148',
            'deal_type'     => '1',
            'is_split'      => '0',
            'sub_deal_code' => '714462209341192864,714462209342192864',

        ];
        $this->json('post', '/api', $body, $this->apiHeaders())
            ->seejson([
                          'code'   => 200,
                          'data'   => [],
                          'status' => 'api-success',
                      ]);
    }

    public function apiHeaders()
    {
        return [
            'Customer'        => 'adidas',
            'Maketplace-Type' => 'tmall',
            'Simulation'      => 1, // 模拟请求
            'lang'            => 'en',
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

    }
}