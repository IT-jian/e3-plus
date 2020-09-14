<?php


namespace App\Services;


use App\Models\GetTaobaoTrade;
use App\Models\TaobaoCloudTrade;
use App\Models\TradeDownTimer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaobaoRdsTrade
{
    private $rdsName = 'rds01';

    public function down_trade_list($params)
    {
        if ($this->checkDownloadRunning()) {
            \Log::error('已经有计划任务正在进行，请稍后再试');
            return true;
        }
        $this->getTradeList($params);

        $this->setDownloadFinish();

        return true;
        // update TradeCounter
    }

    public function checkDownloadRunning()
    {
        $status = Cache::get('is_taobao_rds_trade_running', 0);

        return $status;
    }

    public function setDownloadRunning()
    {
        $status = Cache::forever('is_taobao_rds_trade_running', 1);
    }

    public function setDownloadFinish()
    {
        $status = Cache::forget('is_taobao_rds_trade_running');
    }

    public function checkTrade($trade)
    {
        // 判断状态
        if ($trade['status']) {
        }
        // 判断店铺名称

        return true;
    }
    // taobao_cloud_trade_fetching
    // 下载新增订单
    public function getTradeList($params = array())
    {
        $where = [
            'task' => 'taobao_trade',
            'rds' => 'rds01',
        ];
        $timer = TradeDownTimer::where($where)->first();
        if (empty($timer)) {
            Log::error('down_load_timer 不存在，请设置', $where);
            return false;
        }
        $select = ['tid','type','status', 'seller_nick', 'jdp_modified'];
        $hasNext = true;
        $total = 0;
        do {
            $lastTime = $timer['last_time'];
            $lastKeyId = $timer['last_key_id'];
            $limit = $timer['max_row'];
            $endTime = date('Y-m-d H:i:s', time()-10);
            if (isset($params['tid'])) {
                $where = [];
                $where[] = ['tid', $params['tid']];
                $tradeList = TaobaoCloudTrade::select($select)->where($where)->orderBy('jdp_modified')->limit($limit)->get();
            } else if ($lastKeyId > 0) {
                $where = [];
                $where[] = ['jdp_modified', $lastTime];
                $where[] = ['tid', $lastKeyId];
                $tradeList = TaobaoCloudTrade::select($select)->where($where)->orderBy('jdp_modified')->limit($limit)->get();
                if (empty($tradeList)) {
                    $where = [];
                    $where[] = ['jdp_modified', '>=', $lastTime];
                    $where[] = ['jdp_modified', '<=', $endTime];
                    $tradeList = TaobaoCloudTrade::select($select)->where($where)->orderBy('jdp_modified')->limit($limit)->get();
                }
            } else {
                $where = [];
                $where[] = ['jdp_modified', '>', $lastTime];
                $where[] = ['jdp_modified', '<=', $endTime];
                $tradeList = TaobaoCloudTrade::select($select)->where($where)->orderBy('jdp_modified')->limit($limit)->get();
            }
            if ($tradeList->count() == 0) {
                break;
            }
            if ($limit > count($tradeList)) {
                $hasNext = false;
            }
            $total += count($tradeList);
            $tids = $tradeList->pluck('tid')->toArray();
            $existTradeMap = GetTaobaoTrade::whereIn('tid', $tids)->get(['tid','type','status','jdp_modified','rds','sync_status'])
                ->keyBy('tid');
            $updateData = $insertData = [];
            foreach ($tradeList as $trade) {
                $trade = $trade->toArray();
                $trade['sync_status'] = '0';
                $trade['rds'] = 'rds01';
                if (isset($existTradeMap[$trade['tid']])) {
                    $existTrade = $existTradeMap[$trade['tid']];
                    if (in_array($existTrade->sync_status, [3, 9])) {
                        Log::info('exist_trade in wrong status'.$existTrade->sync_status);
                        // 直接放入队列
                        // Queue::later(60, TaobaoTradeDownJob::class, $trade);
                        continue;
                    }if ($existTrade->jdp_modified == $trade['jdp_modified']){
                        Log::info($existTrade->tid . 'exist_trade in wrong modified '.$existTrade->jdp_modified . ' == ' . $trade['jdp_modified']);
                        continue;
                    } else {
                        $updateData[] = $trade;
                    }
                } else {
                    $insertData[] = $trade;
                }
            }
            try {
                if ($insertData) {
                    DB::table('get_taobao_trade')->insert($insertData);
                }
                if ($updateData) {
                    foreach ($updateData as $data) {
                        DB::table('get_taobao_trade')->where(['tid' => $data['tid']])->update($data);
                    }
                }
            } catch (\Exception $e) {
                Log::error('get_taobao_trade 新增/更新失败: '. $e->getMessage());
                break;
            }

            // 更新timer
            //同一秒数据超过取出行数的，记录最大淘宝交易号
            $first = $tradeList->first()->toArray();
            $first_time = $first['jdp_modified'];
            $last = $tradeList->last()->toArray();
            $timer['last_time'] = $last['jdp_modified'];
            $timer['last_key_id'] = $limit == count($tradeList) && $first_time == $timer['last_time'] ? $last['tid'] : 0;
            if (empty($params['tid'])) {
                DB::table('trade_down_timer')->where(['id' => $timer['id']])->update(['last_time' => $timer['last_time'], 'last_key_id' => $timer['last_key_id']]);
            }
        } while ($hasNext);

        return true;
    }

    /**
     * @return string
     *
     * @author linqihai
     * @since 2019/12/3 13:58
     */
    public function getRdsName()
    {
        return $this->rdsName;
    }

    /**
     * @param string $rdsName
     * @return $this
     *
     * @author linqihai
     * @since 2019/12/3 13:58
     */
    public function setRdsName(string $rdsName)
    {
        $this->rdsName = $rdsName;

        return $this;
    }
}