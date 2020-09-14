<?php


namespace App\Services\Adaptor\Taobao\Downloader;


use App\Models\Sys\Shop;
use App\Services\Adaptor\Contracts\DownloaderContract;
use App\Services\Adaptor\Taobao\Api\Exchange;
use App\Services\Adaptor\Taobao\Jobs\ExchangeBatchTransferJob;
use App\Services\Adaptor\Taobao\Repository\TaobaoExchangeRepository;
use Exception;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class ExchangeDownloader implements DownloaderContract
{

    /**
     * @var TaobaoExchangeRepository
     */
    private $repository;

    public function __construct(TaobaoExchangeRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 换货单下载
     *
     * @param $params
     * @return bool|int
     * @throws Exception
     *
     * @author linqihai
     * @since 2020/1/10 15:42
     */
    public function download($params)
    {
        $disputeIds = $params['dispute_ids'] ?? '';
        $shopCode = $params['shop_code'] ?? '';
        if (empty($shopCode)){
            throw new InvalidArgumentException('params shop_code required!');
        }
        $shop = Shop::getShopByCode($shopCode);

        $exchangeDetails = (new Exchange($shop))->detailMulti($disputeIds);

        $where[] = ['dispute_id', 'in', $disputeIds];
        $existExchanges = $this->repository->getAll($where, ['dispute_id', 'origin_modified']);
        if (!$existExchanges->isEmpty()) {
            $existExchanges = $existExchanges->keyBy('dispute_id');
        }
        $updateTrades = $insertTrades = $formatData = $failDisputeId = [];
        foreach ($exchangeDetails as $disputeId => $originExchange) {
            $exchange = data_get($originExchange,'tmall_exchange_get_response.result.exchange',[]);
            if (empty($exchange)) {
                // 数据不存在则不报错
                if ('DISPUTE_NOT_EXIST' != data_get($originExchange,'tmall_exchange_get_response.msg_code','')) {
                    $failDisputeId[] = $disputeId;
                }
                continue;
            }
            // update check exist
            if (!$existExchanges->isEmpty() && isset($existExchanges[$disputeId])) {
                if ($existExchanges[$disputeId]->origin_modified >= strtotime($exchange['modified'])) {
                    // continue;
                }
                $updateTrades[$disputeId] = $existExchanges[$disputeId];
            } else {
                $insertTrades[] = $disputeId;
            }
            $formatData[$disputeId] = $this->format($exchange, $originExchange);
        }
        if ($formatData) {
            $updateFields = ['status', 'origin_content', 'origin_modified', 'sync_status', 'updated_at', 'created', 'modified'];
            $this->repository->insertMulti($formatData, $updateFields);
            // 组织转入参数
            $params['dispute_ids'] = array_keys($formatData);
            dispatch(new ExchangeBatchTransferJob($params));
        }

        if ($failDisputeId) {
            throw new Exception('fail download exchagnes', [$failDisputeId]);
        }

        return true;
    }

    /**
     * 格式转换
     * @param $exchange
     * @return array
     *
     * @author linqihai
     * @since 2019/12/23 9:35
     */
    public function format($exchange,$origin)
    {
        return [
            'dispute_id'         => $exchange['dispute_id'],
            'seller_nick'     => $exchange['seller_nick'],
            'biz_order_id'     => $exchange['biz_order_id'],
            'status'          => $exchange['status'],
            'origin_content'  => json_encode($origin),
            'origin_created'  => strtotime($exchange['created']),
            'origin_modified' => strtotime($exchange['modified']),
            'created'         => $exchange['created'],
            'modified'        => $exchange['modified'],
            'sync_status'     => 0,// 未转入
            'created_at'      => Carbon::now()->toDateTimeString(),
            'updated_at'      => Carbon::now()->toDateTimeString(),
        ];
    }
}
