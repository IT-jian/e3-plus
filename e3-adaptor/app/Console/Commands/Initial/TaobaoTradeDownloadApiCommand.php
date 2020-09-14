<?php


namespace App\Console\Commands\Initial;


use App\Facades\TopClient;
use App\Models\Sys\Shop;
use App\Services\Adaptor\Taobao\Jobs\TaobaoTradeBatchTransferJob;
use App\Services\Adaptor\Taobao\Jobs\TradeApiBatchDownloadJob;
use App\Services\Platform\Taobao\Client\Top\Request\TradesSoldGetRequest;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use League\Csv\Writer;

class TaobaoTradeDownloadApiCommand extends Command
{
    protected $signature = 'adaptor:taobao:trade_download_api
                            {--shop_code= : The names of the shop to download}
                            {--status= : WAIT_BUYER_PAY：等待买家付款，WAIT_SELLER_SEND_GOODS：等待卖家发货，SELLER_CONSIGNED_PART：卖家部分发货，WAIT_BUYER_CONFIRM_GOODS：等待买家确认收货，TRADE_BUYER_SIGNED：买家已签收（货到付款专用），TRADE_FINISHED：交易成功，TRADE_CLOSED：交易关闭，TRADE_CLOSED_BY_TAOBAO：交易被淘宝关闭，TRADE_NO_CREATE_PAY：没有创建外部交易（支付宝交易），WAIT_PRE_AUTH_CONFIRM：余额宝0元购合约中，PAY_PENDING：外卡支付付款确认中，ALL_WAIT_PAY：所有买家未付款的交易（包含：WAIT_BUYER_PAY、TRADE_NO_CREATE_PAY），ALL_CLOSED：所有关闭的交易（包含：TRADE_CLOSED、TRADE_CLOSED_BY_TAOBAO），PAID_FORBID_CONSIGN，该状态代表订单已付款但是处于禁止发货状态。}
                            {--create_from= : 下载开始时间 yyy-mm-dd hh:ii:ss}
                            {--create_to= : 下载结束时间 yyy-mm-dd hh:ii:ss}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '从淘宝API下载指定创建时间范围内的[等待卖家发货]的订单';

    public function handle()
    {
        if ($this->hasOption('create_from') && !empty($this->option('create_from'))) {
            $from = $this->option('create_from');
        } else {
            $from = Carbon::now()->subWeek()->toDateTimeString();
        }

        if ($this->hasOption('create_to') && !empty($this->option('create_to'))) {
            $to = $this->option('create_to');
        } else {
            $to = Carbon::now()->toDateTimeString();
        }

        $this->info('create_from' . $from);
        $this->info('create_to' . $to);
        if ($this->hasOption('status') && !empty($this->option('status'))) {
            $status = $this->option('status');
        } else {
            $status = 'WAIT_SELLER_SEND_GOODS';
        }
        $shopCodes = [];
        if ($this->hasOption('shop_code') && !empty($this->option('shop_code'))) {
            $shopCodes[] = $this->option('shop_code');
        } else {
            $shops = Shop::available('taobao')->get()->toArray();
            foreach ($shops as $shop) {
                $shopCodes[] = $shop['code'];
            }
        }
        if (empty($shopCodes)) {
            $this->info('empty shop codes');

            return;
        }

        // 如果超过了1天，则每1天为一个分段查询，避免分页过大
        $start = strtotime($from);
        $end = strtotime($to);
        do {
            $endTemp = $start + 24*60*60;
            if ($endTemp > $end) {
                $endTemp = $end+1;
            }
            $timeRange[] = [
                'start' => $start,
                'end' => $endTemp,
            ];
            $start = $endTemp;
        } while ($end > $start);

        krsort($timeRange);
        $filePath = $this->getExportFile(Carbon::parse($from)->format('YmdHi')."-" . Carbon::parse($to)->format('YmdHi'));
        $csv = Writer::createFromPath($filePath, 'w+');
        $csv->insertOne([Writer::BOM_UTF8 . 'tid', 'created', 'shopCode']);

        $bar = $this->output->createProgressBar(count($timeRange)*count($shopCodes));
        $bar->start();
        foreach ($shopCodes as $shopCode) {
            foreach ($timeRange as $time) {
                $bar->advance();
                $page = 1;
                $start = Carbon::createFromTimestamp($time['start'])->toDateTimeString();
                $end = Carbon::createFromTimestamp($time['end'])->toDateTimeString();
                $this->info("shop:{$shopCode} {$start} - {$end}");
                $hasNext = false;
                do {
                    $exportData = $tids = [];
                    $request = new TradesSoldGetRequest();
                    $request->setFields('tid,created');
                    $request->setStartCreated($start);
                    $request->setEndCreated($end);
                    $request->setStatus($status);
                    $request->setPageSize('100');
                    $request->setUseHasNext('true');
                    $request->setPageNo((string)$page);
                    $response = TopClient::shop($shopCode)->execute($request);
                    $trades = data_get($response, 'trades_sold_get_response.trades.trade', []);
                    $this->info("shop:{$shopCode} page:{$page} found:" . count($trades));
                    if (!empty($trades)) {
                        foreach ($trades as $trade) {
                            $tids[] = $trade['tid'];
                            $exportData[] = [
                                $trade['tid'], $trade['created'], $shopCode
                            ];
                        }
                        if ($exportData) {
                            $csv->insertAll($exportData);
                        }
                        $key = 'trade api download ' . $shopCode;
                        $params = ['tids' => $tids, 'shopCode' => $shopCode, 'platform' => 'taobao', 'key' => $key];
                        dispatch((new TradeApiBatchDownloadJob($params))->chain(
                            [
                                new TaobaoTradeBatchTransferJob(['tids' => $tids, 'key' => $key]),
                            ]));
                    }
                    $hasNext = data_get($response, 'trades_sold_get_response.has_next', false);
                    $page++;
                } while ($hasNext);
            }
        }
        $bar->finish();
    }

    public function getExportFile($fileName)
    {
        $fileName = $this->getFileName($fileName);

        if (!File::isDirectory(storage_path('adaptor_export/'))) {
            File::makeDirectory(storage_path('adaptor_export/'));
        }

        return storage_path('adaptor_export/') . $fileName;
    }

    public function getFileName($fileName)
    {
        $prefix = 'init_trade_api_download';

        $string = Carbon::now()->format('YmdHi');

        return $prefix . '_' . $fileName. '_' .$string . '_.csv';
    }
}
