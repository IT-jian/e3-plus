<?php

namespace App\Jobs;

use App\Models\TaobaoTrade;
use App\Services\Adaptor\Taobao\Jobs\TaobaoTradeBatchTransferJob;
use Carbon\Carbon;

class TaobaoTradeTransferJob extends Job
{
    private $tid;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tid)
    {
        $this->tid = $tid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $where = [];
        $where['status'] = 0;
        $where[] = ['updated_at', '<', Carbon::now()->subMinutes(30)->toDateTimeString()];
        $where[] = ['updated_at', '>=', Carbon::now()->subMinutes(60)->toDateTimeString()];
        $total = TaobaoTrade::where($where)->count();
        if ($total) {
            $tids = TaobaoTrade::where($where)->get(['tid'])->toArray();
            dispatch(new TaobaoTradeBatchTransferJob(['tids' => $tids, 'key' => 'taobao-trade-transfer-schedule']));
        }
    }
}
