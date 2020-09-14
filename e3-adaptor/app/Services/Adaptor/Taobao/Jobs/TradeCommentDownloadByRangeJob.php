<?php


namespace App\Services\Adaptor\Taobao\Jobs;


use App\Facades\Adaptor;
use App\Models\Sys\Shop;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Taobao\Api\TradeRates;
use Exception;
use Illuminate\Support\Carbon;

class TradeCommentDownloadByRangeJob extends BaseDownloadJob
{
    private $params;

    /**
     * TradeCommentDownloadByRangeJob constructor.
     *
     * @param $params
     */
    public function __construct($params)
    {
        $this->params = $params   ;
    }

    /**
     * @throws \Exception
     *
     * @author linqihai
     * @since 2020/1/18 16:38
     */
    public function handle()
    {
        $shop = Shop::where($this->params['shop_code'])->first();
        $where = [
            'page'           => 1,
            'page_size'      => 50,
            'start_modified' => Carbon::createFromTimestamp($this->params['start_time'])->toDateTimeString(),
            'end_modified'   => Carbon::createFromTimestamp($this->params['end_time'])->toDateTimeString(),
        ];
        $ratesServer = (new TradeRates($shop));
        $page = 1;
        do {
            $where['page'] = $page;
            \Log::info('trade comment download start', $where);
            // 查询列表
            $response = $ratesServer->page($where);
            if (empty($response)) {
                break;
            }
            if ($rates = data_get($response, 'trade_rates.trade_rate', [])) {
                try { // 直接下载
                    Adaptor::platform('taobao')->download(AdaptorTypeEnum::COMMENTS, $rates);
                } catch (Exception $exception) {
                    dispatch(new TradeCommentDownloadJob($rates));
                }
            }
            if ($hasNext = data_get($response, 'has_next', false)) {
                $page++;
            } else {
                break;
            }
        } while (true);
        \Log::info('trade comment download end');
    }

    /**
     * 获取分配给这个任务的标签
     *
     * @return array
     */
    public function tags()
    {
        return ['taobao_comments_download'];
    }
}
