<?php


namespace App\Jobs\Timer\PopTaobao;


use App\Facades\TopClient;
use App\Models\Sys\Shop;
use App\Models\SysStdTrade;
use App\Services\Adaptor\Taobao\Repository\TaobaoInvoiceRepository;
use App\Services\Hub\Jobs\TradeInvoiceDetailQueryAndCreateBatchJob;
use App\Services\Platform\Taobao\Client\Top\Request\TmcMessagesConfirmRequest;
use App\Services\Platform\Taobao\Client\Top\Request\TmcMessagesConsumeRequest;
use App\Services\Platform\Taobao\Client\Top\Request\TmcUserPermitRequest;
use App\Services\PlatformDownloadConfigServer;
use App\Services\ShopDownloadConfigServer;
use Cache;
use Exception;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
use Illuminate\Support\Carbon;
use Log;

/**
 * 开票申请消息监听
 *
 * Class InvoiceApplyNoticeTimer
 * @package App\Jobs\Timer\PopTaobao
 */
class InvoiceApplyNoticeTimer extends CronJob
{
    public function interval()
    {
        return 1000 * 10;// 每 10 秒运行一次
    }

    public function isImmediate()
    {
        return false;// 是否立即执行第一次，false则等待间隔时间后执行第一次
    }

    public function run()
    {
        $name = 'taobao_invoice_apply_sync_jobs';
        $platformConfigServer = new PlatformDownloadConfigServer($name);
        $config = $platformConfigServer->getConfig();
        if (isset($config) && 1 == $config['stop_download']) { // 停止下载，则不再处理
            return true;
        }

        // 查询系统商店单据
        $sellerNicks = [];
        $shops = Shop::available('taobao')->get()->toArray();
        foreach ($shops as $shop) {
            $sellerNicks[] = $shop['seller_nick'];
        }

        if (empty($sellerNicks)) {
            Log::info('not available shop seller nick for invoice apply notice timer');

            return true;
        }
        $result = true;
        $end = strtotime('-10 seconds');
        foreach ($shops as $shop) {
            // 店铺级别下载设置
            try {
                $configServer = new ShopDownloadConfigServer($name, $shop['code']);
            } catch (Exception $e) {
                continue;
            }
            $config = $configServer->getConfig();
            if (isset($config) && 1 == $config['stop_download']) { // 停止下载，则不再处理
                continue;
            }
            // 锁名
            $lockName = $configServer->getConfigLockCacheKey();
            // 查询页大小
            $pageSize = isset($config['query_page_size']) && $config['query_page_size'] > 0 ? $config['query_page_size'] : 50;

            /**
             * @var \Illuminate\Cache\RedisLock $lock
             */
            $lock = Cache::lock($lockName, 10 * 60);
            try {
                if ($lock->acquire()) {
                    $topClient = TopClient::shop($shop['code']);
                    $request = new TmcUserPermitRequest();
                    $response = $topClient->execute($request);
                    if (true == data_get($response, 'tmc_user_permit_response.is_success', false)) {
                        do {
                            $request = new TmcMessagesConsumeRequest();
                            $request->setQuantity($pageSize);
                            $response = $topClient->execute($request);
                            $message = data_get($response, 'tmc_messages_consume_response.messages.tmc_message', []);
                            if (empty($message)){
                                break;
                            }
                            Log::debug('invoice apply found' . count($message));
                            $messageIds = $this->processMessage($message, $shop['seller_nick']);
                            if ($messageIds) {
                                $request = new TmcMessagesConfirmRequest();
                                $request->setsMessageIds($messageIds);
                                $response = $topClient->execute($request);
                            } else { // 如果都沒处理，表示代码异常跳出循环
                                break;
                            }
                        } while (true);
                    }
                    $configServer->setNextQueryAt($end);
                }
            } catch (\Exception $e) {
                Log::debug(__CLASS__ . $e->getMessage());
            } finally {
                $lock->release();
            }
        }

        $platformConfigServer->setNextQueryAt($end);

        return $result;
    }

    public function processMessage($messages, $sellerNick)
    {
        $messageIds = $formatApply = $applyIds = $tids = [];
        foreach ((array)$messages as $message) {
            if (!in_array($message['topic'], ['alibaba_invoice_Apply'])) {
                continue;
            }
            $apply = json_decode($message['content'], true);
            $apply['seller_nick'] = $sellerNick;
            $applyId = $apply['apply_id'] ?? '';
            if (empty($applyId)) {
                // 更新为 buyer_cancel 则不再推送
                if ('buyer_cancel' == $apply['trigger_status']) {
                    $data = ['push_status' => 2, 'error_msg' => 'buyer_cancel'];
                    $where = [
                        'platform_tid' => $apply['platform_tid'],
                        'push_status' => 0
                    ];
                    (new TaobaoInvoiceRepository())->update($data, $where);
                }
                // info('found invoice abnormal '.$message['content'], [$apply]);
                continue;
            }
            // 开票申请的类型是否需要过滤
            // 开票申请的触发类型，buyer_payed= 卖家已付款，sent_goods=卖家已发货， buyer_confirm=买家确认收货， refund_seller_confirm=卖家同意退款， invoice_supply=买家申请补开发票， invoice_change=买家申请改抬头， change_paper=电换纸
            $formatApply[]= $this->formatApply($apply);
            $messageIds[] = $message['id'];
            $applyIds[$apply['platform_tid']] = $apply['apply_id'];
            $tids[] = $apply['platform_tid'];
        }
        if (!empty($formatApply)) {
            $updateFields = [
                'platform_code',
                'trigger_status',
                'business_type',
            ];
            (new TaobaoInvoiceRepository())->insertMulti($formatApply, $updateFields);

            $jobApplyIds = [];
            $pushTrades = SysStdTrade::where('tid', $tids)
                ->where('pay_time', '>=', '2020-07-21 10:00:00')
                ->where('status', 'TRADE_FINISHED')
                ->get(['tid']);
            foreach ($pushTrades as $trade) {
                if (isset($applyIds[$trade['tid']])) {
                    $jobApplyIds[] = $applyIds[$trade['tid']];
                }
            }
            if ($jobApplyIds) {
                dispatch(new TradeInvoiceDetailQueryAndCreateBatchJob($jobApplyIds));
            }
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
            'next_query_at' => Carbon::now()->toDateTimeString(),
            'created_at' => Carbon::now()->toDateTimeString(),
        ];
    }
}
