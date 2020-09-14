<?php

namespace App\Http\Controllers;

use App\Facades\HubApi;
use App\Models\SysStdExchangeItem;
use App\Models\SysStdRefund;
use App\Models\SysStdRefundItem;
use App\Models\SysStdTradeItem;
use App\Models\TaobaoRefund;
use App\Services\Adaptor\Jingdong\Api\Sku;
use League\Csv\Writer;
use Illuminate\Support\Facades\File;
use App\Facades\Adaptor;
use App\Facades\JosClient;
use App\Models\Sys\Shop;
use App\Models\SysStdExchange;
use App\Models\SysStdPushConfig;
use App\Models\SysStdPushQueue;
use App\Models\SysStdTrade;
use App\Models\TaobaoTrade;
use App\Notifications\DingtalkNotification;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Jingdong\Api\Refund;
use App\Services\Adaptor\Jingdong\Jobs\JingdongStepTradeBatchTransferJob;
use App\Services\Adaptor\Jingdong\Jobs\JingdongTradeBatchTransferJob;
use App\Services\Adaptor\Jingdong\Jobs\RefundDownloadJob;
use App\Services\Adaptor\Jingdong\Repository\Rds\JingdongRdsTradeRepository;
use App\Services\Adaptor\Jobs\PushQueueCreateJob;
use App\Services\Adaptor\Taobao\Api\Exchange;
use App\Services\Adaptor\Taobao\Events\TaobaoExchangeUpdateEvent;
use App\Services\Adaptor\Taobao\Events\TaobaoRefundUpdateEvent;
use App\Services\Adaptor\Taobao\Jobs\BatchDownload\ExchangeBatchDownloadJob;
use App\Services\Adaptor\Taobao\Jobs\BatchDownload\ItemBatchDownloadJob;
use App\Services\Adaptor\Taobao\Jobs\BatchDownload\RefundBatchDownloadJob;
use App\Services\Adaptor\Taobao\Jobs\BatchDownload\TradeBatchDownloadJob;
use App\Services\Adaptor\Taobao\Jobs\ExchangeTransferJob;
use App\Services\Adaptor\Taobao\Jobs\RefundBatchTransferJob;
use App\Services\Adaptor\Taobao\Jobs\TaobaoItemBatchTransferJob;
use App\Services\Adaptor\Taobao\Jobs\TaobaoTradeBatchTransferJob;
use App\Services\Adaptor\Taobao\Repository\Rds\TaobaoRdsItemRepository;
use App\Services\Hub\Adidas\Request\TradeCreateRequest;
use App\Services\Adaptor\Taobao\Repository\Rds\TaobaoRdsTradeRepository;
use App\Services\Hub\Jobs\SysStdPushBatchJob;
use App\Services\Hub\Jobs\TradeInvoiceDetailQueryAndCreateJob;
use App\Services\Hub\PushQueueFormatType;
use App\Services\Platform\Jingdong\Client\Jos\Request\AscFreightViewRequest;
use App\Services\Platform\Jingdong\Client\Jos\Request\AscQueryViewRequest;
use App\Services\Platform\Jingdong\Client\Jos\Request\AscReceiveListRequest;
use App\Services\Platform\Jingdong\Client\Jos\Request\AscServiceAndRefundViewRequest;
use App\Services\Platform\Taobao\Client\Top\Request\ExchangeGetRequest;
use App\Services\Platform\Taobao\Client\Top\TopClientNew;
use Calchen\LaravelDingtalkRobot\Message\TextMessage;
use Calchen\LaravelDingtalkRobot\Robot;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Psr7\stream_for;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redis;
use Log;

class TestController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index($method = null)
    {
        if (empty($method)) {
            return \App\Models\User::first();
        }
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return 'method no found';
    }

    public function batchPush()
    {
        $limit = 100;
        $configs = SysStdPushConfig::where('stop_push', 0)->orderBy('id')->get();
        foreach ($configs as $config) {
            $where = [
                ['status', 0],
                ['method', $config['method']],
            ];
            $sql = SysStdPushQueue::where($where)->toSql();
            $exist = SysStdPushQueue::where($where)->first();
            if ($exist) {
                SysStdPushQueue::select(['id'])->where($where)->orderBy('try_times')->lockForUpdate()
                    ->chunkById($limit, function ($queues) use ($config) {
                        $ids = $queues->pluck('id');
                        SysStdPushQueue::whereIn('id', $queues->pluck('id'))->update(['status' => 3]);
                        dispatch(new SysStdPushBatchJob($ids, $config));
                    });
            }
        }
    }

    public function refundDownload()
    {
        $refundIds = [
            '16551301156129268',
        ];
        $result = dispatch_now(new RefundBatchDownloadJob(['refund_ids' => $refundIds, 'platform' => 'taobao', 'key' => 'test-refund-download']));
        \Log::debug('test', [$result]);

        return 1;
    }

    public function refundTransfer()
    {
        $refundIds = [
            '16579239968699327',
        ];
        $result = dispatch_now(new RefundBatchDownloadJob(['refund_ids' => $refundIds, 'platform' => 'taobao', 'key' => 'test-refund-download']));
        $result = dispatch_now(new RefundBatchTransferJob(['refund_ids' => $refundIds, 'platform' => 'taobao', 'key' => 'test-refund-download']));
        \Log::debug('test', [$result]);

        return 1;
    }

    public function dingtalk()
    {
        $msg = new TextMessage('test message');
        // new Robot($name) $name 为 config 中配置的内容
        Notification::send(new Robot(), new DingtalkNotification($msg));
    }

    public function down_trade()
    {
        $rdsTrade = new \App\Services\TaobaoRdsTrade();
        $rdsTrade->getTradeList();

        return 'finish';
    }

    public function pop_trade()
    {
        Artisan::call('oms:get_trade_taobao');

        return 'call oms:get_trade_taobao';
    }

    public function test()
    {
        $staff = Shop::first();

        return $staff;
    }

    /**
     * 淘宝单个请求
     * @author linqihai
     * @since 2019/12/30 18:18
     */
    public function top()
    {
        $shopCode = 'SCADET_ANTAFXTBOB';
        $disputeId = '46436801647121912';
        $shop = Shop::where('code', $shopCode)->firstOrFail()->toArray();
        try {
            $result = (new Exchange($shop))->detail($disputeId);
        } catch (\Exception $e) {
            return [$e->getMessage(), $e->getCode()];
        }

        return $result;
    }

    /**
     * 淘宝接口多个并发请求
     *
     * @return array|mixed
     *
     * @author linqihai
     * @since 2019/12/31 11:25
     */
    public function tops()
    {
        $shopCode = 'SCADET_ANTAFXTBOB';
        $disputeIds = ['46436801647121912', '46519297032588314'];
        $fields = 'dispute_id,bizorder_id,num,buyer_nick,status,created,modified,reason,title,buyer_logistic_no,
        seller_logistic_no,refund_version,refund_phase,good_status,price,bought_sku,exchange_sku,buyer_address,
        address,time_out,buyer_phone,buyer_logistic_name,seller_logistic_name,alipay_no,buyer_name,seller_nick,desc';

        $requests = [];
        foreach ($disputeIds as $disputeId) {
            $request = new ExchangeGetRequest();
            $request->setFields($fields);
            $request->setDisputeId($disputeId);
            $requests[] = $request;
        }
        /**
         * @var $client TopClientNew
         */
        $client = app(TopClientNew::class);
        $shop = Shop::where('code', $shopCode)->firstOrFail()->toArray();
        $result = $client->shop($shop)->execute($requests, $shop['access_token']);

        return $result;
    }

    public function hubs()
    {
        $tids = [
            '236523118285534019', '236671151019658102', '237307046372353696', '237307046372353696',
            '241389774830422525', '241911437355411821', '250078432508100530', '252021313575160037',
        ];
        $requests = [];
        foreach ($tids as $tid) {
            $trade = SysStdTrade::where('tid', $tid)->first();
            $request = app(TradeCreateRequest::class);
            $requests[$tid] = $request->setContent($trade->toArray());
        }
        $config = config('hubclient.clients.adidas');
        $client = new \App\Services\Hub\Adidas\AdidasClient($config);
        $result = $client->execute($requests);

        return $result;
    }

    public function has_sbc()
    {
        $str = '中国ｐｈｐ12';

        return containSbc($str);
    }

    public function refund()
    {
        $refund = [
            'refund_id'       => '16198566972749884',
            'platform'        => 'taobao',
            'tid'             => '276785924461748498',
            'has_good_return' => '1',
            'status'          => 'SUCCESS',
            'order_status'    => 'SUCCESS',
            'company_name'    => '百世快递',
            'sid'             => '70171537135361',
        ];
        $origin = [
            'refund_id'       => '16198566972749884',
            'platform'        => 'taobao',
            'tid'             => '276785924461748498',
            'has_good_return' => '1',
            'status'          => 'SUCCESS',
            'order_status'    => 'SUCCESS',
            'company_name'    => '百世快递',
            'sid'             => '70171537135361',
        ];
        // 订单取消
        $refund['status'] = 'SUCCESS';
        $refund['order_status'] = 'WAIT_SELLER_SEND_GOODS';
        Event::dispatch(new TaobaoRefundUpdateEvent($refund, $origin));
        // 退单创建
        $refund['status'] = 'WAIT_BUYER_RETURN_GOODS';
        $refund['order_status'] = 'WAIT_BUYER_CONFIRM_GOODS';
        $refund['has_good_return'] = '1';
        Event::dispatch(new TaobaoRefundUpdateEvent($refund, $origin));
        // 退单取消
        $refund['status'] = 'CLOSED';
        Event::dispatch(new TaobaoRefundUpdateEvent($refund, $origin));
        $refund['has_good_return'] = '1';
        // gwc 新增
        $refund['has_good_return'] = '0';
        $refund['status'] = 'SUCCESS';
        $refund['order_status'] = 'WAIT_BUYER_CONFIRM_GOODS';
        Event::dispatch(new TaobaoRefundUpdateEvent($refund, $origin));
        // gwc 取消
        $refund['has_good_return'] = '0';
        $refund['status'] = 'CLOSED';
        $refund['order_status'] = 'WAIT_BUYER_CONFIRM_GOODS';
        Event::dispatch(new TaobaoRefundUpdateEvent($refund, $origin));
        // 快递更新
        $origin['company_name'] = '';
        $origin['sid'] = '';
        Event::dispatch(new TaobaoRefundUpdateEvent($refund, $origin));
    }

    public function exchange()
    {
        $exchange = SysStdExchange::firstOrFail();
        $origin = $exchange = $exchange->toArray();
        // 新建
        $exchange['status'] = 'WAIT_BUYER_RETURN_GOODS';
        Event::dispatch(new TaobaoExchangeUpdateEvent($exchange, $origin));
        // 取消
        $exchange['status'] = 'CLOSED';
        Event::dispatch(new TaobaoExchangeUpdateEvent($exchange, $origin));
        // 物流单号更新
        $exchange['status'] = 'WAIT_BUYER_CONFIRM_GOODS';
        $origin['buyer_logistic_name'] = '';
        $origin['buyer_logistic_no'] = '';
        Event::dispatch(new TaobaoExchangeUpdateEvent($exchange, $origin));
    }

    public function timer()
    {
        $count = 5000;
        $where = [
            ['jdp_modified', '>=', '2019-06-13 15:07:41'],
            ['jdp_modified', '<', '2019-06-14 15:07:41'],
        ];
        $rds = new TaobaoRdsTradeRepository();
        $total = $rds->builder()->where($where)->count();
        \Log::info('总共需要下载 total：' . $total, $where);
        if ($total) {
            $result = $rds->builder()->select(['tid'])->where($where)->orderBy('jdp_modified')->chunk($count, function ($results, $page) use ($where) {
                \Log::info("当前第 {$page} 页");
                $results->pluck('tid')->chunk(500)->each(function ($tids, $k) use ($page) {
                    $key = "page-$page-chunk-$k";
                    \Log::info("job [$key] pop");
                    dispatch((new TradeBatchDownloadJob(['tids' => $tids, 'platform' => 'taobao', 'key' => $key]))->chain(
                        [
                            new TaobaoTradeBatchTransferJob(['tids' => $tids, 'key' => $key]),
                        ]));
                });
            });
        }
        \Log::info('下载完成');
    }

    public function trade_transfer()
    {
        $count = 5000;
        $where = [];
        // $where['sync_status'] = 0;
        $total = TaobaoTrade::where($where)->count();
        \Log::info('总共需要下载 total：' . $total, $where);
        if ($total) {
            $result = TaobaoTrade::select(['tid', 'origin_modified'])->where($where)->orderBy('origin_modified')->chunk($count, function ($results, $page) {
                $first = Carbon::createFromTimestamp($results->first()->origin_modified);
                $last = Carbon::createFromTimestamp($results->last()->origin_modified);
                \Log::info("当前第 {$page} 页. from {$first} to {$last}");
                $results->pluck('tid')->chunk(500)->each(function ($tids, $k) use ($page) {
                    $key = "page-$page-chunk-$k";
                    \Log::info("job [$key] pop");
                    dispatch((new TaobaoTradeBatchTransferJob(['tids' => $tids, 'key' => $key]))->delay(30));
                });
            });
        }
        \Log::info('下载完成');
    }

    public function testFile()
    {
        $client = new Client();
        $filePath = "/home/vagrant/Code/laravel/lumen-swoole/public/static/img/404.a57b6f3.png";
        $response = $client->post('http://lumen-swoole.test/testLoadFile',
                                  [
                                      'multipart' => [
                                          [
                                              'name'     => 'myfile',
                                              'contents' => fopen($filePath, 'r'),
                                          ],
                                      ],
                                  ]
        );

        return $response->getStatusCode();
    }

    public function testLoadFile(Request $request)
    {
        \Log::info('message');
        if ($request->hasFile('myfile')) {
            if ($request->file('myfile')->isValid()) {
                // 上传成功
                // 随机名字 . 后缀
                $fileName = "other/" . Date("YmdHis") . substr(md5(time()), 5, 15) . "." . $request->file("file")->extension();// 需要 开启php_fileinfo 扩展 否则会报错
                // 获取临时上传的路径（如果不存在本地，则方法调用完之后，会被删除）
                //$fileUrl = $request->file('file')->path();
                // 可选存储在本地
                \Storage::put($fileName, $request->file("file"));

                // $fileUrl = $request->file("file")->move(__DIR__."/",$fileName);

                return response()->json($fileName);
            }
        }

        return response()->json('success');
    }

    public function testJingdongRefundDownload()
    {
        $refundTrades = $this->jingdongRefunds();
        $shop = Shop::where('code', 'jingdong-test')->firstOrFail()->toArray();
        foreach ($refundTrades as $refundTrade) {
            $refundTrade['shop'] = $shop;
            dispatch_now(new RefundDownloadJob($refundTrade));
        }
    }

    public function jingdongRefunds()
    {
        // 列表
        $json = '[{"customerName":"丁天歌","applyTime":1578664268000,"customerExpectName":"退货","wareName":"FILA 斐乐官方 男子羽绒服 2019冬季新款运动满印短款羽绒服男 传奇蓝-NV 180/100A/XL","serviceId":724578811,"customerExpect":10,"skuType":4,"serviceStatus":10005,"customerMobile":"188****2724","serviceStatusName":"待收货","skuId":59156590170,"customerGrade":50,"customerPin":"jd_4c9c41396a05b","pickwareAddress":"山东济宁市金乡县城区文化路北段金乡汽车站东侧OYO锦东商务宾馆前台","wareNum":1,"skuTypeName":"套装中的单品","wareType":10,"orderId":109113048299}]';
        // 详情
        $detailJson = '{"questionTypeCid1Name":"","applyId":531087170,"expectPickwareType":4,"approveResult":31,"applyDetailList":[{"skuUuid":"40_33246375373_37323_59156590170_1_0","wareTypeName":"主商品","wareName":"FILA 斐乐官方 男子羽绒服 2019冬季新款运动满印短款羽绒服男 传奇蓝-NV 180/100A/XL","skuType":4,"skuId":59156590170,"applyDetailId":906851657,"skuTypeName":"套装中的单品","wareType":10}],"serviceBillDetailList":[{"serviceDetailId":985757958,"wareCid3":12104,"wareBrand":"斐乐(FILA)","skuUuid":"40_33246375373_37323_59156590170_1_0","wareCid1":1318,"payPrice":2480,"wareCid2":12102,"skuType":4,"wareName":"FILA 斐乐官方 男子羽绒服 2019冬季新款运动满印短款羽绒服男 传奇蓝-NV 180/100A/XL","skuId":59156590170,"actualPayPrice":1419,"wareNum":1,"wareType":10}],"expectPickwareTypeName":"上门取件","applyTime":1578664268000,"questionTypeCid2":19,"serviceCount":1,"returnWareType":10,"questionPic":"","approveNotes":"您好，商品收到之日起7天内完好（即能够保持原有品质、功能，商品本身、配件、商标标识齐全），我们可为您提供七天无理由退换货政策服务。请您注明订单号并快递至本店。并及时将快递单号上传到服务单页面，如未及时上传，服务单将自动取消。勿用顺丰及任何快递到付方式。商品如核实完好，我们会尽快处理，否则将原物返回，运费自行承担。如退货，订单如有赠品的，请一并退货。为更好处理您的订单，请申请退换货后，当天寄出快递。","questionTypeCid1":19,"hasPackage":false,"returnWareAddress":{"villageCode":39390,"provinceCode":13,"cityCode":2900,"countyCode":2917,"detailAddress":"山东济宁市金乡县城区文化路北段金乡汽车站东侧OYO锦东商务宾馆前台"},"approvePin":"Fila官方旗舰店cs18","customerExpect":10,"serviceStatusName":"待收货","approveDate":1578664294000,"pickwareAddress":{"villageCode":39390,"provinceCode":13,"cityCode":2900,"countyCode":2917,"detailAddress":"山东济宁市金乡县城区文化路北段金乡汽车站东侧OYO锦东商务宾馆前台"},"approveName":"Fila官方旗舰店cs18","jdUpgradeSuggestion":"快递公司： 京东快递 运单号： JDY000240428005，此单寄回商品非本店商品，故操作拒签，已联系京东客服升230000号小苹果级处","orderTypeName":"POPSOP","orderId":109113048299,"returnWareTypeName":"自营配送","orderType":22,"approveResultName":"上门取件","pickwareTypeName":"上门取件","questionTypeCid2Name":"","refundTypeName":"原返","pickwareType":4,"questionDesc":"买错了","sysVersion":9,"customerInfo":{"jdPin":"jd_4c9c41396a05b","contactInfo":{"villageCode":0,"contactName":"丁天歌","provinceCode":0,"cityCode":0,"contactMobile":"188****2724","countyCode":0},"name":"丁天歌","grade":50},"updateDate":1579314901000,"refundType":20,"customerExpectName":"退货","updateName":"朱亚涔","serviceId":724578811,"serviceStatus":10005,"extJsonStr":"{\"afsPay\":\"3000000000000000000001000000000010000000000000000000000000000000000000000000000000000000000000000000\",\"goldManager\":\"0\",\"isUat\":\"0\",\"isTuiHuanWuYou\":\"5\",\"payingMember\":\"0\",\"APPLYREASON\":\"七天无理由\",\"geekpay\":\"00000050000000000000000000000000000000000000000000\"}","afsContactInfo":{"villageCode":0,"contactName":"京东旗舰欧少川","provinceCode":0,"cityCode":0,"contactMobile":"177****8776","contactTel":"177****8776","countyCode":0,"contactZipcode":"","detailAddress":"福建泉州市晋江市陈埭镇安踏物流园1号仓1楼京东Fila旗舰退货组"},"companyId":6}';
        // 运费
        $shipping = '{"freightLogList":[{"content":"创建运费记录","freightId":387084553,"createDate":1578750936000,"createName":"青龙消息"}],"repeatFreightFlag":true,"shipWayId":2087,"finalFreightMoney":0,"deliveryDate":1578750936000,"expressCode":"JDY000240428005","modifiedMoney":0,"expressCompany":"京东快递","freightMoney":0}';

        $json = '[{"customerName":"曹石磊","applyTime":1578822175000,"customerExpectName":"退货","wareName":"FILA 斐乐官方 女子卫衣 2019冬季新款格纹LOGO休闲拉链套头衫罩衫卫衣女 樱花红-LP 165/84A/M","serviceId":725573759,"customerExpect":10,"skuType":4,"serviceStatus":10005,"customerMobile":"136****8689","serviceStatusName":"待收货","skuId":56012114767,"customerGrade":105,"customerPin":"caoshilei0513","pickwareAddress":"上海浦东新区周浦镇横桥路69弄13号楼702室","wareNum":1,"skuTypeName":"套装中的单品","wareType":10,"orderId":109239687311}]';
        return json_decode($json, true);
    }

    public function initPushQueue()
    {
        $limit = 500;
        $count = 0;
        do {
            $where = [];
            $trades = SysStdTrade::where($where)->limit($limit)->get(['tid']);
            if ($trades->isNotEmpty()) {
                $queues = [];
                foreach ($trades as $trade) {
                    $queues[] = [
                        'bis_id'     => $trade['tid'],
                        'platform'   => 'taobao',
                        'hub'        => 'adidas',
                        'method'     => 'tradeCreate',
                        'status'     => 3,
                        'extends'    => json_encode([]),
                        'created_at' => \Illuminate\Support\Carbon::now()->toDateTimeString(),
                        'push_content' => '',
                        'push_version' => 0,
                    ];
                }
                foreach (array_chunk($queues, 50) as $chunk) {
                    if (PushQueueFormatType::is(PushQueueFormatType::WHEN_PUSH_TO_QUEUE)) {
                        dispatch_now(new PushQueueCreateJob($chunk));
                    } else {
                        SysStdPushQueue::insert($chunk);
                    }
                }
            }
            $count += count($trades);
            if (count($trades) < $limit) {
                break;
            }
        } while (true);
    }

    public function qimen_address()
    {

        $api = new \App\Services\Platform\Taobao\Qimen\Api\AddressSelfModifyApi();
        $content = [
            'bizOrderId'      => app(Request::class)->get('tid'),
            'modifiedAddress' => [
                'name'          => '小张三',
                'province'      => '福建省',
                'city'          => '厦门市',
                'area'          => '湖里区',
                'town'          => '金山社区',
                'addressDetail' => 'new address',
                'postCode'      => '360000',
                'phone'         => '15200000000',
            ],
        ];
        return $api->execute($content);
    }

    public function batch_qimen()
    {
        $ids = [1, 2];
        $config = SysStdPushConfig::where('method', 'tradeCreate')->orderBy('push_sort')->first();
        $config['proxy'] = 'qimen';
        return dispatch_now((new SysStdPushBatchJob($ids, $config, sprintf('%s-%s-%s', 1, 1, 1)))->tries($config['tries'])->delay($config['delay']));
    }

    public function taobao_exchange_page()
    {
        $shop = Shop::first();
        $page = 1;
        $where = [
            'page'           => $page,
            'page_size'      => 100,
            'start_modified' => '2020-08-10 09:05:00',
            'end_modified'   => '2020-08-10 09:10:00',
        ];
        $exchangeServer = (new Exchange($shop));
        $exchanges = $exchangeServer->page($where);

        return $exchanges;
        $disputeIds = [];
        foreach ($exchanges as $exchange) {
            $disputeIds[] = $exchange['dispute_id'];
        }
        // 分发任务
        $params = ['dispute_ids' => $disputeIds, 'shop_code' => $shop['code'], 'key' => $page];
        dispatch_now(new \App\Services\Adaptor\Taobao\Jobs\ExchangeBatchTransferJob($disputeIds));;

        return $exchanges;
    }

    public function taobao_comment()
    {
        $shop = Shop::first();
        $page = 1;
        $where = [
            'page'           => $page,
            'page_size'      => 100,
            'start_modified' => '2020-06-29 00:00:00',
            'end_modified'   => '2020-06-30 00:00:00',
        ];
        $ratesServer = (new \App\Services\Adaptor\Taobao\Api\TradeRates($shop));
        $rate_page = [];
        do {
            $where['page'] = $page;
            // 查询列表
            $response = $ratesServer->page($where);
            if (empty($response)) {
                break;
            }
            $rate_page[$page] = $response;
            if ($rates = data_get($response, 'trade_rates.trade_rate', [])) {
                try { // 直接下载
                    Adaptor::platform('taobao')->download(AdaptorTypeEnum::COMMENTS, $rates);
                } catch (\Exception $exception) {
                }
            }
            if ($hasNext = data_get($response, 'has_next', false)) {
                $page++;
            } else {
                break;
            }
            if ($page == 3) {
                break;
            }
        } while (true);
        return $rate_page;
    }

    public function taobao_item()
    {
        $rds = new TaobaoRdsItemRepository();
        $shop = Shop::first();
        $where = [
            ['jdp_modified', '>=', Carbon::createFromTimestamp('2020-06-29 00:00:00')->toDateTimeString()],
            ['jdp_modified', '<', Carbon::createFromTimestamp('2020-06-30 00:00:00')->toDateTimeString()],
        ];
        $result = $rds->builder()->select(['num_iid'])->where($where)->where('nick', $shop['seller_nick'])
            ->orderBy('jdp_modified')->chunk(5000, function ($results, $page) use ($where) {
                $results->pluck('num_iid')->chunk(500)->each(function ($numIids, $k) use ($page) {
                    $key = "page-$page-chunk-$k";
                    // rds 下载，下载之后直接格式化转入
                    dispatch_now(new ItemBatchDownloadJob(['num_iids' => $numIids, 'platform' => 'taobao', 'key' => $key]));
                });
            });

        return 'success';
    }

    public function queue_test()
    {
        $queue = 'queues:taobao_download';
        $job =Redis::connection('redis')->eval(
            \Illuminate\Queue\LuaScripts::pop(), 3, $queue, $queue.':reserved', $queue.':notify',
            Carbon::now()->addRealSeconds(10)->getTimestamp()
        );

        return $job;
    }

    public function test_job()
    {
        // dispatch(new RdsTradeBatchTimer(['key' => 'test', 'tids' => [596903143482335798]]));
        dispatch(new \App\Jobs\TestJob([1,2,3]));
    }

    public function jingdong_comment()
    {
        $shop = Shop::first();
        $params = [
            'start_modified' => '2020-07-01 00:00:00',
            'end_modified' => '2020-07-02 00:00:00',
        ];
        $result = (new \App\Services\Adaptor\Jingdong\Api\VenderComments($shop))->page($params);

        return $result;
    }

    public function jingdong_trade()
    {
        $shop = Shop::first();
        $where = [
            ['pushModified', '>=', '2020-07-01 00:00:00'],
            ['pushModified', '<', '2020-07-01 10:00:00'],
        ];
        $rds = new \App\Services\Adaptor\Jingdong\Repository\Rds\JingdongRdsTradeRepository();
        $orderIds = $rds->builder()->where($where)->where('venderId', $shop['seller_nick'])->limit(20)->get()->pluck('orderId')->toArray();
        dispatch(new \App\Services\Adaptor\Jingdong\Jobs\BatchDownload\TradeBatchDownloadJob(['order_ids' => $orderIds, 'platform' => 'jingdong', 'key' => 'test']));

        return $orderIds;
    }

    public function jingdong_refund()
    {
        $where = [
            'page'           => 1,
            'page_size'      => 20,
            'start_modified' => '2020-07-01 00:00:00',
            'end_modified'   => '2020-07-01 00:00:10',
        ];

        $shop = Shop::first();


        $refundApi = new Refund($shop);

        return $refundApi->count($where);
        $request = new AscReceiveListRequest();
        $request->setBuId($shop['seller_nick']);
        $request->setOperatePin('adaptor');
        $request->setOperateNick('adaptor');
        $request->setApplyTimeBegin('2020-07-01 00:00:00');
        $request->setApplyTimeEnd('2020-07-01 01:00:10');
        $request->setPageNumber(1);
        $request->setPageSize(10);
        $response = JosClient::shop($shop['code'])->execute($request, $shop['access_token']);
        return $response;
    }

    public function jingdong_refund_detail()
    {
        $shop = Shop::where('code', 'EMC6')->first();
        $refundApi = new Refund($shop);
        $refunds = $refundApi->find('828247137');
        $refund = $refunds[0];
        $requests = [];
        // 订单详情
        $request = new AscQueryViewRequest();
        $request->setServiceId($refund['serviceId']);
        $request->setOrderId($refund['orderId']);
        $request->setBuId($shop['seller_nick']);
        $request->setOperatePin('adaptor');
        $requests['detail'] = $request->setOperateNick('adaptor');

        // 退款
        $request = new AscServiceAndRefundViewRequest();
        $requests['refund'] = $request->setOrderId($refund['orderId']);

        // 运单信息
        $request = new AscFreightViewRequest();
        $request->setServiceId($refund['serviceId']);
        $request->setOrderId($refund['orderId']);
        $request->setBuId($shop['seller_nick']);
        $request->setOperatePin('adaptor');
        $requests['ship'] = $request->setOperateNick('adaptor');

        $response = JosClient::shop($shop['code'])->execute($requests, $shop['access_token']);

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
            $serviceBillDetail = data_get($detail, 'serviceBillDetailList');
            foreach ($serviceBillDetail as $bill) {
                $detail['refoundAmount'] += $bill['actualPayPrice'];
            }
        }

        return $detail;
    }

    public function jingdong_trans()
    {
        dispatch(new \App\Services\Adaptor\Jingdong\Jobs\JingdongStepTradeBatchTransferJob(['order_ids' => ['126464758596']]));
    }

    public function pop_taobao_comments()
    {
        $shop = Shop::first();
        $startTime = strtotime('2020-07-01 00:00:00');
        $endTime = strtotime('2020-07-04 00:00:00');
        $step = 60 * 10;
        do {
            print_r("start_at:$startTime end_at:$endTime\r\n");
            $stepEnd = $startTime + $step;
            $where = [
                'shop_code'  => $shop['shop_code'],
                'start_time' => $startTime,
                'end_time'   => $stepEnd,
            ];
            dispatch(new \App\Services\Adaptor\Taobao\Jobs\TradeCommentDownloadByRangeJob($where));
            if ($stepEnd > $endTime) {
                break;
            }
            $startTime = $stepEnd;
        } while (true);
    }
    public function pop_jingdong_comments()
    {
        $shop = Shop::first();
        $startTime = strtotime('2020-07-04 00:00:00');
        $endTime = strtotime('2020-07-05 00:00:00');
        $step = 60 * 10;
        do {
            print_r("start_at:$startTime end_at:$endTime\r\n");
            $stepEnd = $startTime + $step;
            $where = [
                'shop_code'  => $shop['shop_code'],
                'start_time' => $startTime,
                'end_time'   => $stepEnd,
            ];
            dispatch(new \App\Services\Adaptor\Jingdong\Jobs\VenderCommentDownloadByRangeJob($where));
            if ($stepEnd > $endTime) {
                break;
            }
            $startTime = $stepEnd;
        } while (true);
    }

    public function jingdong_items()
    {
        $pageSize = 10;
        $shop = Shop::first();
        $where = [
            'page'           => 1,
            'page_size'      => $pageSize,
            'start_modified' => '2020-07-01 00:00:00',
            'end_modified'   => '2020-07-02 00:00:00',
            'shop_code'   => $shop['code'],
        ];

        $skuServer = new \App\Services\Adaptor\Jingdong\Api\ItemTotal($shop);
        $total = $skuServer->count($where);
        print_r("total: $total ---");
        if ($total) {
            $totalPage = (int)ceil($total / $pageSize);
            foreach (range(1, $totalPage) as $page) {
                $where['page'] = $page;
                Adaptor::platform('jingdong')->download(AdaptorTypeEnum::ITEM_BATCH, $where);
                if (3 == $page) {
                    break;
                }
            }
        }
    }

    public function jingdong_item_refresh()
    {
        $where = ['size' => ''];
        $items = \DB::table('sys_std_platform_sku')->selectRaw('distinct num_iid, shop_code')->where($where)->get();
        foreach ($items as $item) {
            dispatch(new \App\Services\Adaptor\Jingdong\Jobs\ItemDownloadJob(['ware_id' => $item->num_iid, 'shop_code'=> $item->shop_code]));
        }

        return 'success';
    }

    public function jingdong_item_transformer()
    {
        $where = ['size' => ''];
        $items = \DB::table('sys_std_platform_sku')->selectRaw('distinct num_iid, shop_code')->where($where)->get();
        foreach ($items as $item) {
            $params = ['ware_id' => $item->num_iid, 'shop_code' => $item->shop_code];
            Adaptor::platform('jingdong')->transfer(AdaptorTypeEnum::ITEM, $params);
        }
    }

    public function delete_jingdong_item()
    {
        $where = ['size' => ''];
        $items = \DB::table('sys_std_platform_sku')->select(['num_iid', 'outer_id'])->where($where)->get();
        foreach ($items as $item) {
            $count = \DB::table('sys_std_platform_sku')->where('is_delete', 0)->where('outer_id', $item->outer_id)->where('num_iid', $item->num_iid)->count();
            if ($count > 1) {
                \DB::table('sys_std_platform_sku')->where('size', '')->where('is_delete', 0)->where('outer_id', $item->outer_id)->where('num_iid', $item->num_iid)->delete();
            }
        }
    }

    public function jingdong_refund_update()
    {
        $shop = Shop::first();
        $page = 3;
        $pageSize = 50;
        $start = '2020-07-17 00:00:00';
        $end = '2020-07-21 04:00:00';

        $request = new \App\Services\Platform\Jingdong\Client\Jos\Request\AscSyncListRequest();
        $request->setBuId($shop['seller_nick']);
        $request->setOperatePin('adaptor');
        $request->setOperateNick('adaptor');
        $request->setServiceStatus('10005');
        $request->setUpdateTimeBegin($start);
        $request->setUpdateTimeEnd($end);
        $request->setPageNumber($page);
        $request->setPageSize($pageSize);
        $response = JosClient::shop($shop['code'])->execute($request, $shop['access_token']);

        return $response;
    }

    public function jingdong_exchange_trans()
    {
        $refund = \App\Models\JingdongRefund::where('service_id', '805954632')->first();
        $refund['shop_code'] = 'EMC5';
        Adaptor::platform('jingdong')->transfer(AdaptorTypeEnum::EXCHANGE, $refund);
    }

    public function jingdong_trade_api()
    {
        $shop = Shop::first();
        $request = new \App\Services\Platform\Jingdong\Client\Jos\Request\PopOrderGetRequest();
        $request->setOrderId('129097217889');
        $request->setOptionalFields('orderId,venderId,orderType,payType,orderTotalPrice,orderSellerPrice,orderPayment,freightPrice,sellerDiscount,orderState,orderStateRemark,deliveryType,invoiceEasyInfo,invoiceInfo,invoiceCode,orderRemark,orderStartTime,orderEndTime,consigneeInfo,itemInfoList,couponDetailList,venderRemark,balanceUsed,pin,returnOrder,paymentConfirmTime,waybill,logisticsId,vatInfo,modified,directParentOrderId,parentOrderId,customs,customsModel,orderSource,storeOrder,idSopShipmenttype,scDT,serviceFee,pauseBizInfo,taxFee,tuiHuoWuYou,orderSign,storeId,menDianId,mdbStoreId,salesPin,originalConsigneeInfo');
        $response = JosClient::shop($shop['code'])->execute($request, $shop['access_token']);

        return $response;
    }

    public function jingdong_trade_download()
    {
        Adaptor::platform('jingdong')->download('tradeApi', ['order_id' =>'126903957162', 'shop_code' => 'EMC5']);
        Adaptor::platform('jingdong')->transfer(AdaptorTypeEnum::TRADE, ['order_id' => '126903957162', 'shop_code' => 'EMC5']);
    }

    public function jingdong_comment_trade()
    {
        $where = [
            ['creation_time', '>=', '2020-07-04 00:00:00'],
            ['creation_time', '<', '2020-07-05 00:00:00'],
        ];
        $tids = \App\Models\JingdongComment::where($where)->get(['order_id'])->pluck('order_id')->toArray();
        foreach (array_chunk($tids, 500) as $chunkTids) {
            $existTids = SysStdTrade::whereIn('tid', $chunkTids)->get(['tid']);
            if ($existTids->isEmpty()) {
                $fetchTids = $chunkTids;
            } else {
                $fetchTids = array_diff($chunkTids, $existTids->pluck('tid')->toArray());
            }
            foreach ($fetchTids as $fetchTid) {
                try {
                    Adaptor::platform('jingdong')->download('tradeApi', ['order_id' =>$fetchTid, 'shop_code' => 'EMC5']);
                    Adaptor::platform('jingdong')->transfer(AdaptorTypeEnum::TRADE, ['order_id' => $fetchTid, 'shop_code' => 'EMC5']);
                } catch (\Exception $e) {
                    print_r('Fail:' . $fetchTid . $e->getMessage() . "\r\n");
                }
            }
            print_r('fetch total' . $fetchTids . '\r\n');
        }
    }

    public function taobao_trans_pop()
    {
        \App\Models\TaobaoExchange::select(['dispute_id'])->where('sync_status', 0)->whereIn('status', ['买家已退货，待收货', '待买家退货'])->chunk(50, function ($results) {
            $tids = $results->pluck('dispute_id')->toArray();
            dispatch(new \App\Services\Adaptor\Taobao\Jobs\ExchangeBatchTransferJob(['dispute_ids' => $tids, 'key' => 'pop-transformer']));
        });
    }

    public function jingdong_sku()
    {
        $shop = Shop::first();
        $request = new \App\Services\Adaptor\Jingdong\Api\Sku($shop);
        $skuId = '71528157052';

        $response = $request->find($skuId);

        return $response;
    }

    public function refresh_jingdong_item()
    {
        $repository = new \App\Services\Adaptor\Jingdong\Repository\JingdongItemRepository();
        $items = $repository->getAll([], ['ware_id', 'vender_id'], 'origin_modified asc');
        foreach ($items as $item) {
            dispatch(new \App\Services\Adaptor\Jingdong\Jobs\ItemDownloadJob(['shop_code' => 'EMC5', 'ware_id' => $item->ware_id]));
            break;
        }
    }

    public function pop_jingdong_item_trans()
    {
        $items = \App\Models\JingdongItem::where('sync_status', 0)->get(['ware_id', 'vender_id']);
        foreach (array_chunk($items->pluck('ware_id')->toArray(), 500) as $chunk) {
            $params = [
                'ware_ids' => $chunk,
                'shop_code' => 'EMC5',
            ];
            dispatch(new \App\Services\Adaptor\Jingdong\Jobs\JingdongItemBatchTransferJob($params));
        }
    }

    public function test_voc_upload()
    {
        $fileName = 'test_baison_upload.csv';
        $path = storage_path('adaptor_export/') . $fileName;
        return \Illuminate\Support\Facades\Storage::disk('sftp')->putFileAs('/', $path, $fileName, 'public');
    }

    public function test_inventory_upload()
    {
        $fileName = 'test_baison_upload.csv';
        $path = storage_path('adaptor_export/') . $fileName;
        $result = \Illuminate\Support\Facades\Storage::disk('inventory')->putFileAs('/ODP/platform_inventory_cn/', $path, $fileName, 'public');

        return !$result ? 'upload fail' : $result;
    }

    public function invoice_apply()
    {
        $shop = Shop::first();

        $topClient = \App\Facades\TopClient::shop($shop['code']);
        $request = new \App\Services\Platform\Taobao\Client\Top\Request\TmcUserPermitRequest();
        $request->setTopics('alibaba_invoice_Apply');
        $response = $topClient->execute($request);
        Log::info('tmc_invoice_apply:user permit', [$response]);
        if (true == data_get($response, 'tmc_user_permit_response.is_success', false)) {
            do {
                $request = new \App\Services\Platform\Taobao\Client\Top\Request\TmcMessagesConsumeRequest();
                $request->setQuantity('10');
                $response = $topClient->execute($request);
                Log::info('tmc_invoice_apply:consume messages', [$response]);
                $message = data_get($response, 'tmc_messages_consume_response.messages.tmc_message', []);
                if (empty($message)){
                    break;
                }
                $messageIds = $this->processMessage($message, $shop['seller_nick']);
                if ($messageIds) {
                    $request = new \App\Services\Platform\Taobao\Client\Top\Request\TmcMessagesConfirmRequest();
                    $request->setsMessageIds($messageIds);
                    $response = $topClient->execute($request);
                    Log::info('tmc_invoice_apply:confirm messages', [$response]);
                } else { // 如果都沒处理，表示代码异常跳出循环
                    break;
                }

                break;
            } while (true);
        }
    }

    public function processMessage($messages, $sellerNick)
    {
        $messageIds = $formatApply = $applyIds = [];
        foreach ((array)$messages as $message) {
            if (!in_array($message['topic'], ['alibaba_invoice_Apply'])) {
                continue;
            }
            $apply = json_decode($message['content'], true);
            $apply['seller_nick'] = $sellerNick;
            $formatApply[]= $this->formatApply($apply);
            $messageIds[] = $message['id'];
            $applyIds[] = $apply['apply_id'];
        }
        if (!empty($formatApply)) {
            (new \App\Services\Adaptor\Taobao\Repository\TaobaoInvoiceRepository())->insertMulti($formatApply);
            dispatch(new \App\Services\Hub\Jobs\TradeInvoiceDetailQueryAndCreateBatchJob($applyIds));
        }

        return $messageIds;
    }

    public function formatApply($apply)
    {
        return [
            'apply_id' => $apply['apply_id'],
            'seller_nick' => $apply['seller_nick'],
            'platform_tid' => $apply['platform_tid'],
            'platform_code' => $apply['platform_code'],
            'trigger_status' => $apply['trigger_status'],
            'business_type' => $apply['business_type'],
            'next_query_at' => \Illuminate\Support\Carbon::now()->toDateTimeString(),
        ];
    }

    public function trade_api_download()
    {
        $params = ['tids' => ['1129588771918267278', '1129323969830238850'], 'shopCode' => 'EMC2'];
        $result =  Adaptor::platform('taobao')->download('tradeApi', $params);

        return $result ? 'success' : 'false';
    }

    public function order_split_amount_multi()
    {
        $params = [
            'order_ids' => ['122510054355', '122511457950', '128238915620'],
            'shop_code' => 'EMC5'
        ];

        $result = Adaptor::platform('jingdong')->download(AdaptorTypeEnum::JD_ORDER_SPLIT_AMOUNT, $params);

        return 'result';
    }

    public function test_env()
    {
        $liveDate = config('hubclient.cutover_date', '');
        return strtotime($liveDate);
    }

    public function invoice_find()
    {
        $shop = Shop::getShopByNick('adidas官方旗舰店');
        try {
            return (new \App\Services\Adaptor\Taobao\Api\Invoice($shop))->find('598576039612553795');
        } catch (\Exception $e) {
            if ($e instanceof \App\Services\Platform\Taobao\Client\Top\Exceptions\TaobaoTopServerSideException) {
                if ('isv.apply-not-exists' == $e->getSubErrorCode()) {
                }
            }
        }
    }

    public function invoice_job()
    {
        dispatch_now(new \App\Services\Hub\Jobs\TradeInvoiceDetailQueryAndCreateJob('01_8iYjX1VE5aRsl8pCe_zrqomKQsueJ1YDm4ftt3koEI'));

        return '1';
    }

    public function invoice_update()
    {
        $server = new \App\Services\AisinoInvoiceServer();
        $invoiceApplies = \App\Models\TaobaoInvoiceApply::where('query_status', 1)->get();
        foreach ($invoiceApplies as $invoiceApply) {
            $server->fetchApply($invoiceApply);
        }
    }

    public function export_exchange_return()
    {
        $filePath = storage_path('adaptor_export/') . 'export_exchange_return' . time() . '.csv';
        File::put($filePath, '');
        $titles = [Writer::BOM_UTF8.'dispute_id', 'refund_id', 'tid', 'oid', 'std trade out sku id', 'exchange_sku id', 'std  trade sku id', 'taobao refund sku id', 'std refund sku id', 'tag'];
        $csv = Writer::createFromPath($filePath, 'w+');
        $csv->insertOne($titles);

        $exchangeItems = SysStdExchangeItem::all();
        foreach ($exchangeItems as $exchangeItem) {
            $refundItem = SysStdRefundItem::where('oid', $exchangeItem['oid'])->first();
            if ($refundItem['refund_id']) { // 退货
                $refund = SysStdRefund::where('refund_id', $refundItem['refund_id'])->where('has_good_return', 1)->first();
                if ($refund['refund_id']) {
                    $taobaoRefund = TaobaoRefund::where('refund_id', $refund['refund_id'])->first();
                    if ($taobaoRefund) {
                        $originRefundSku = data_get($taobaoRefund, 'origin_content.refund_get_response.refund.sku', '');
                        $originRefundSku = explode('|', $originRefundSku)[0];
                        $tradeItem = SysStdTradeItem::where('tid', $refund['tid'])->where('oid', $refund['oid'])->first();
                        $equal = 1;
                        if ($originRefundSku != $tradeItem['sku_id']) {
                            $equal = 2;
                        }
                        $csv->insertOne([$exchangeItem['dispute_id'], $refundItem['refund_id'], $refund['tid'], $refundItem['oid'], $tradeItem['outer_sku_id'], $exchangeItem['exchange_sku'], $tradeItem['sku_id'], $originRefundSku, $refundItem['sku_id'], $equal]);
                    }
                }
            }
        }
    }

    public function export_exchange_return_v2()
    {
        $filePath = storage_path('adaptor_export/') . 'v2export_exchange_return' . time() . '.csv';
        File::put($filePath, '');
        $titles = [
            Writer::BOM_UTF8.'dispute_id', 'bought_sku', 'bought_outer_sku_id', 'exchange_sku', 'exchange_outer_sku_id', 'tid', 'orderNo'
            , 'dispute_orderNo', 'refund_orderNo', 'oid', 'has_goods_return', 'refund_id', 'refund_sku_id', 'refund_outer_sku_id',
            'exchange_status', 'refund_status', 'exchange_created', 'refund_created', 'is_same'
        ];
        $csv = Writer::createFromPath($filePath, 'w+');
        $csv->insertOne($titles);
        SysStdPushQueue::where('platform', 'taobao')->whereIn('method', ['exchangeCreate', 'exchangeCreateExtend'])->chunk('200', function ($results) use ($csv) {
            $disputeIds = $results->pluck('bis_id')->toArray();
            $exchangeItems = SysStdExchangeItem::whereIn('sys_std_exchange_item.dispute_id', $disputeIds)->leftJoin('sys_std_exchange', function ($join) {
                $join->on('sys_std_exchange_item.dispute_id', '=', 'sys_std_exchange.dispute_id');
            })->where('sys_std_exchange.status', 'SUCCESS')->get()->keyBy('oid');
            $oids = $exchangeItems->pluck('oid')->toArray();
            $exchangeSkuIds = $exchangeItems->pluck('exchange_sku')->toArray();
            $boughtSkuIds = $exchangeItems->pluck('bought_sku')->toArray();
            $skuMap = \App\Models\SysStdPlatformSku::whereIn('sku_id', array_merge($exchangeSkuIds, $boughtSkuIds))->get()->keyBy('sku_id');
            $refundItems = SysStdRefundItem::whereIn('oid', $oids)->get()->keyBy('oid');
            $refundIds = $refundItems->pluck('refund_id')->toArray();
            $refunds = SysStdRefund::whereIn('refund_id', $refundIds)->get()->keyBy('oid')->toArray();
            $data = [];
            $base = new \App\Services\Hub\Adidas\Request\Transformer\BaseTransformer();
            foreach ($exchangeItems as $oid => $exchangeItem) {
                if (isset($refundItems[$oid]) && isset($refunds[$oid])) {
                    if ('CLOSED' == $refunds[$oid]['status']) {
                        continue;
                    }
                    $data[] = [
                        'dispute_id' => '\''.$exchangeItem['dispute_id'],
                        'bought_sku' => '\''.$exchangeItem['bought_sku'],
                        'bought_outer_sku_id' => '\''.$skuMap[$exchangeItem['bought_sku']]['outer_id'] ?? '',
                        'exchange_sku' => '\''.$exchangeItem['exchange_sku'],
                        'exchange_outer_sku_id' => '\''.$skuMap[$exchangeItem['exchange_sku']]['outer_id'] ?? '',
                        'tid' => '\''.$refunds[$oid]['tid'] ?? '',
                        'orderNo' => '\''.$base->generatorOrderNo($refunds[$oid]['tid'], 'taobao'),
                        'dispute_orderNo' => '\''.$base->generatorExchangeNo($exchangeItem['dispute_id'], 'taobao'),
                        'refund_orderNo' => '\''.$base->generatorRefundNo($refunds[$oid]['refund_id'], 'taobao'),
                        'oid' => '\''.$oid,
                        'has_goods_return' => '\''.$refunds[$oid]['has_good_return'] ?? '3',
                        'refund_id' => '\''.$refunds[$oid]['refund_id'],
                        'refund_sku_id' => '\''.$refundItems[$oid]['sku_id'],
                        'refund_outer_sku_id' => '\''.$refundItems[$oid]['outer_sku_id'],
                        'exchange_status' => $exchangeItem['status'] ?? '',
                        'refund_status' => $refunds[$oid]['status'] ?? '',
                        'exchange_created' => $exchangeItem['created'] ?? '',
                        'refund_created' => $refunds[$oid]['created'] ?? '',
                        'is_same' => $exchangeItem['exchange_sku'] == $refundItems[$oid]['sku_id'] ? '相同' : '不同',
                    ];
                }
            }
            if ($data) {
                $csv->insertAll($data);
            }
        });
    }

    public function test_11()
    {
        $tradeItem = [
            'tid' => '111151802571000013',
            'oid' => '1111518025710000130',
        ];
        $trade = TaobaoTrade::where('tid', $tradeItem['tid'])->first();
        $items = data_get($trade, 'origin_content.trade_fullinfo_get_response.trade.orders.order', []);
        foreach ($items as $item) {
            if ($item['oid'] == $tradeItem['oid']) {
                $tradeItem['divide_order_fee'] = $item['divide_order_fee'];
            }
        }

        return $tradeItem;
    }

    public function updateRefundItemNum()
    {
        $filePath = storage_path('adaptor_export/') . 'updateRefundItemNum' . time() . '.csv';
        File::put($filePath, '');
        $titles = [
            Writer::BOM_UTF8. 'refund_id', 'oid', 'tid', 'refund_fee', 'divide_order_fee', 'trade_number', 'refund_number', 'update_number', 'refund_order_sn', 'trade_order_sn', 'is_equal'
        ];
        $csv = Writer::createFromPath($filePath, 'w+');
        $csv->insertOne($titles);
        SysStdPushQueue::where('platform', 'taobao')->whereIn('method', ['refundReturnCreate', 'refundReturnCreateExtend', 'tradeCancel'])->where('status', 1)->chunk('500', function ($results, $page) use ($csv) {
            $data = [];
            $refundIds = $results->pluck('bis_id')->toArray();
            $refunds = SysStdRefund::whereIn('refund_id', $refundIds)->where('status', '<>', 'CLOSED')->get();
            if ($refunds->isNotEmpty()) {
                $refundIds = $refunds->pluck('refund_id')->toArray();
                $tids = $refunds->pluck('tid')->toArray();
                $refundItems = SysStdRefundItem::where('refund_id', $refundIds)->get()->keyBy('oid');
                $oids = $refundItems->pluck('oid')->toArray();
                $tradeItems = SysStdTradeItem::whereIn('tid', $tids)->where('platform', 'taobao')->whereIn('oid', $oids)->where('num', '>', 1)->get();
                if ($tradeItems->isNotEmpty()) {
                    $base = new \App\Services\Hub\Adidas\Request\Transformer\BaseTransformer();
                    $tradeItems = $tradeItems->keyBy('oid');
                    foreach ($refundItems as $oid => $refundItem) {
                        if ($tradeItem = $tradeItems[$oid] ?? []) { // 检查数量是否异常
                            if (empty($tradeItem['divide_order_fee'])) {
                                $data[] = [
                                    'refund_id'        => 'empty-divide',
                                    'oid'              => '',
                                    'tid'              => '',
                                    'refund_fee'       => '',
                                    'divide_order_fee' => '',
                                    'trade_number'     => '',
                                    'refund_number'    => '',
                                    'update_number'    => '',
                                    'refund_order_sn'  => '',
                                    'trade_order_sn'   => '',
                                    'is_equal'         => '4',
                                ];
                                continue;
                            }
                            // 组装更新的数量
                            $number = ceil($refundItem['refund_fee'] / ($tradeItem['divide_order_fee'] / $tradeItem['num']));
                            $data[] = [
                                'refund_id'        => '\''.$refundItem['refund_id'],
                                'oid'              => '\''.$refundItem['oid'],
                                'tid'              => '\''.$tradeItem['tid'],
                                'refund_fee'       => $refundItem['refund_fee'],
                                'divide_order_fee' => $tradeItem['divide_order_fee'],
                                'trade_number'     => $tradeItem['num'],
                                'refund_number'    => $refundItem['num'],
                                'update_number'    => $number,
                                'refund_order_sn'  => $base->generatorRefundNo($refundItem['refund_id'], 'taobao'),
                                'trade_order_sn'   => $base->generatorRefundNo($tradeItem['tid'], 'taobao'),
                                'is_equal'         => $number == $refundItem['num'] ? 1 : 0,
                            ];
                        }
                    }
                }
            }
            if ($data) {
                $csv->insertAll($data);
            } else {
                $emptyRow = [
                    'refund_id'        => 'page-'.$page,
                    'oid'              => '',
                    'tid'              => '',
                    'refund_fee'       => '',
                    'divide_order_fee' => '',
                    'trade_number'     => '',
                    'refund_number'    => '',
                    'update_number'    => '',
                    'refund_order_sn'  => '',
                    'trade_order_sn'   => '',
                    'is_equal'         => '3',
                ];
                $csv->insertOne($emptyRow);
            }
        });
    }

    public function wms_cancel()
    {
        $refundIds = [
            656474639373900011,
            656474639373900012,
            656474639373900013,
            656474639373900014,
            656474639373900017
        ];
        $refundIds = [
            656474639373900021,
            656474639373900022,
            656474639373900023,
            656474639373900024,
            656474639373900025
        ];

        /*$refundIds = [
            656474639373900015,
            656474639373900016
        ];*/
        $queues = \App\Models\AdidasWmsQueue::where('status', 0)->get();
        $refundIds = $queues->pluck('bis_id')->toArray();
        $refundIds = ['7677972119'];
        dispatch_now(new \App\Services\Wms\Jobs\AdidasWmsPushJob($refundIds));

        $refundId = '4949328200500017';

        return '==';
    }

    public function wms_encrypt()
    {
        // $iv = '0000000000000000';
        // $iv = str_repeat("\0", 16);
        $iv = '';
        $privateKey = "123456";
        $data = "{\"orderCode\":\"orderId\",\"status\":1}";
        //加密
        $md5 = md5($privateKey,false);
        $key = substr($md5,8, 16);
        echo "秘钥:\n";
        echo $key;
        $encrypted = openssl_encrypt($data,'aes-128-ecb', $key,OPENSSL_RAW_DATA);
        $encrypted = base64_encode($encrypted);
        echo "密文:\n";
        echo $encrypted;
        echo "\n";
        //解密
        $decrypt = openssl_decrypt(base64_decode($encrypted),'aes-128-ecb',$key,OPENSSL_RAW_DATA);
        echo "明文:\n";
        echo $decrypt;
        echo "sign:\n";
        echo md5('body=' . $encrypted . $privateKey);
    }

    public function auto_exchange()
    {
        $exchanges = \DB::table('tmp_auto_exchanges')->where('status', 0)->get();
        foreach ($exchanges as $exchange) {
            $method = $exchange['method'];
            $content = $exchange['input'];
            try {
                $result = \App\Facades\HubApi::platform('taobao')->execute(compact('method', 'content'));
                \DB::table('tmp_auto_exchanges')->where('id', $exchange['id'])->update(['status' => 1, 'message' => json_encode($result)]);
            } catch (\Exception $e) {
                \DB::table('tmp_auto_exchanges')->where('id', $exchange['id'])->update(['status' => 2, 'message' => $e->getMessage()]);
            }
        }
    }


    public function test_taobao_refund($refundId)
    {
        $refundId = '75365475334201877';
        $shopCode = 'EMC1';
        // 通过 api 获取最新的版本号，没获取到在从rds获取
        $request = new \App\Services\Platform\Taobao\Client\Top\Request\RefundGetRequest();
        $request = $request->setRefundId($refundId);
        $shop = Shop::getShopByCode($shopCode);
        $refund = [];
        try {
            $response = \App\Facades\TopClient::shop($shop['code'])->execute($request, $shop['access_token']);
            $refund = data_get($response, 'refund_get_response.refund', []);
        } catch (\Exception $e) {
            \Log::debug('find refund error' . $e->getMessage());
        }
        if (empty($refund)){
            $rds = new \App\Services\Adaptor\Taobao\Repository\Rds\TaobaoRdsRefundRepository();
            $rdsRefund = $rds->getRow(['refund_id' => $refundId]);
            if (empty($rdsRefund)) {
                return [];
            }
            $originRefund = $rdsRefund->jdp_response;
            if (!is_array($originRefund)) {
                $originRefund = json_decode($originRefund, true);
            }
            $refund = data_get($originRefund, 'refund_get_response.refund', []);
        }

        return $refund;
    }

    public function tmp_exchange_retry()
    {
        $whereMethod = 'e3plus.oms.exchange.returngoods.agree';
        // $whereMethod = 'e3plus.oms.logistics.offline.send'
        $status = 'WAIT_SELLER_CONFIRM_GOODS';
        // $status = 'WAIT_SELLER_SEND_GOODS';
        $method = 'exchangeReturnGoodsAgree';
        // $method = 'tradeOfflineSend';
        $returnAgrees = \DB::table('tmp_exchange_retry')->where('status', 0)->where('method', $whereMethod)->get();
        foreach ($returnAgrees  as $returnAgree) {
            $returnAgree = collect($returnAgree);
            $newExchange = SysStdExchange::where('tid', $returnAgree['tid'])->where('status', $status)->first();
            if (empty($newExchange)) {
                \DB::table('tmp_exchange_retry')->where('id', $returnAgree['id'])->update(['status' => 1, 'message' => json_encode(['无可用数据'])]);
                continue;
            }
            $content = json_decode($returnAgree['content'], true);
            $disputeId = $newExchange['dispute_id'];

            $content['refund_id'] = $disputeId;
            try {
                $result = \App\Facades\HubApi::hub('adidas')->platform('taobao')->execute(compact('method', 'content'));
                \DB::table('tmp_exchange_retry')->where('id', $returnAgree['id'])->update(['status' => 1, 'right_dispute_id' => $disputeId,'message' => json_encode($result)]);
            } catch (\Exception $e) {
                $result[] = $e->getMessage();
                \DB::table('tmp_exchange_retry')->where('id', $returnAgree['id'])->update(['status' => 2, 'right_dispute_id' => $disputeId,'message' => json_encode($result)]);
            }
            break;
        }
    }

    public function tmp_refund_retry()
    {
        $status = 'WAIT_SELLER_CONFIRM_GOODS';
        $method = 'refundReturnGoodsAgreeExtend';
        // $method = 'tradeOfflineSend';
        $returnAgrees = \DB::table('tmp_refund_retry')->where('status', 0)->get();
        foreach ($returnAgrees  as $returnAgree) {
            $returnAgree = collect($returnAgree);
            $refund = SysStdRefund::where('refund_id', $returnAgree['refund_id'])->where('status', $status)->first();
            if (empty($refund)) {
                \DB::table('tmp_refund_retry')->where('id', $returnAgree['id'])->update(['status' => 1, 'message' => json_encode(['无可用数据'])]);
                continue;
            }
            $content = [
                'refund_id' => $returnAgree['refund_id'],
                'shop_code' => $refund['shop_code'],
                'warehouse_status' => 1,
            ];
            try {
                $result = \App\Facades\HubApi::hub('adidas')->platform('taobao')->execute(compact('method', 'content'));
                \DB::table('tmp_refund_retry')->where('id', $returnAgree['id'])->update(['status' => 1, 'message' => json_encode($result)]);
            } catch (\Exception $e) {
                $result[] = $e->getMessage();
                \DB::table('tmp_refund_retry')->where('id', $returnAgree['id'])->update(['status' => 2, 'message' => json_encode($result)]);
            }
            break;
        }
    }

    public function test_inventory_sku()
    {
        $method = 'skusQuantityUpdate';
        $skus = \DB::table('tmp_inventory_retry')->where('status', 2)->get();
        foreach ($skus  as $item) {
            $sku = collect($item);
            $platform = $sku['platform'];
            $content = [
                'shop_code' => $sku['shop_code'],
                'data' => [
                    [
                        'num_iid' => $sku['num_iid'] ?? '',
                         'sku_id' => $sku['sku_id'],
                         'quantity' => $sku['quantity'],
                         'is_item' => "",
                         'type' => $sku['type'] ?? "1",
                    ]
                ]
            ];
            $result = [];
            try {
                $result = \App\Facades\HubApi::hub('adidas')->platform($platform)->execute(compact('method', 'content'));
                \DB::table('tmp_inventory_retry')->where('id', $sku['id'])->update(['status' => 1, 'message' => json_encode($result)]);
            } catch (\Exception $e) {
                $result[] = $e->getMessage();
                \DB::table('tmp_inventory_retry')->where('id', $sku['id'])->update(['status' => 2, 'message' => json_encode($result)]);
            }
            if ('taobao' == $platform) {
                usleep(300);
            } else {
                usleep(50);
            }
        }
    }

    public function update_taobao_sku_quantity()
    {
        $request = new \App\Services\Platform\Taobao\Client\Top\Request\SkusQuantityUpdateRequest();
        $request->setNumIid('624316542172');
        $request->setType('1');
        $request->setSkuidQuantities('4590226631197:0;4590226631198:0');
        $result = \App\Facades\TopClient::shop('EMC3')->execute($request);

        return $result;
    }

    public function test_redis()
    {
        return \App\Models\TaobaoSkusQuantityUpdateQueue::isLimitedByPlatform() ? 1 : 2;

        return 2;
    }

    public function jingdong_recycled_item()
    {
        $shopCode = 'EMC5';
        $fields = [
            'wareId', 'title', 'categoryId', 'brandId', 'wareStatus', 'outerId', 'barCode', 'created', 'modified', 'offlineTime',
            'logo', 'marketPrice', 'costPrice', 'jdPrice', 'brandName', 'shopId',
        ];
        $params = [
            'page' => 1,
            'page_size' => 100,
            'start_modified' => '2020-01-01 00:00:00',
            'end_modified' => '2020-09-03 00:00:00',
        ];
        $request = new \App\Services\Platform\Jingdong\Client\Jos\Request\WareReadSearchWare4RecycledRequest();
        $request->setPageNo($params['page']);
        $request->setPageSize($params['page_size']);
        $request->setField($fields);
        $request->setStartModifiedTime($params['start_modified']);
        $request->setEndModifiedTime($params['end_modified']);
        $result = JosClient::shop($shopCode)->execute($request);

        $recycledWare = data_get($result, 'jingdong_ware_read_searchWare4Recycled_responce.page.data', []);

        $wareIds = [];
        foreach ($recycledWare as $item) {
            $wareIds[] = $item['wareId'];
        }

        if ($wareIds) {
            \App\Models\SysStdPlatformSku::where('platform', 'jingdong')
                ->whereIn('num_iid', array_unique($wareIds))
                ->update(['is_delete' => 1]);
        }

        return implode(',', $wareIds);
    }

    public function jingdong_recycled_sku()
    {
        $shopCode = 'EMC5';
        $fields = [
            'wareId', 'wareTitle', 'wareTitle', 'skuName', 'outerId', 'skuId', 'itemNum', 'barCode', 'status'
            , 'created', 'modified', 'jdPrice', 'stockNum', 'saleAttrs',
        ];
        $request = new \App\Services\Platform\Jingdong\Client\Jos\Request\SkuReadSearchSkuListRequest();
        $request->setWareId('10020229959372');
        $request->setField($fields);
        $request->setPageNo('1');
        $request->setPageSize(10);

        $result = JosClient::shop($shopCode)->execute($request);
        return $result;
    }
}

