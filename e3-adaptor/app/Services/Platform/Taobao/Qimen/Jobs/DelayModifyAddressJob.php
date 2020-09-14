<?php


namespace App\Services\Platform\Taobao\Qimen\Jobs;


use App\Jobs\Job;
use App\Models\SysStdTrade;
use App\Models\TaobaoTrade;
use App\Services\Adaptor\Taobao\Jobs\BatchDownload\TradeBatchDownloadJob;
use App\Services\Adaptor\Taobao\Jobs\TaobaoTradeBatchTransferJob;
use App\Services\Platform\Taobao\Qimen\Api\AddressSelfModifyApi;

class DelayModifyAddressJob extends Job
{
    // public $queue = 'default';
    public $content;

    // 重试三次
    public $tries = 5;
    // 间隔十秒
    public $delay = 60;
    // maxTries

    /**
     * DelayModifyAddressJob constructor.
     *
     * @param $content
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * @author linqihai
     * @since 2020/1/18 16:38
     */
    public function handle()
    {
        $tid = $this->content['bizOrderId'];
        $sysStdTrade = SysStdTrade::where('tid', $tid)->first();
        if (empty($sysStdTrade)) {
            $taobaoTrade = TaobaoTrade::where('tid', $tid)->first();
            if (empty($taobaoTrade)){
                // 立即下载
                dispatch_now(new TradeBatchDownloadJob(['tids' => [$tid], 'platform' => 'taobao', 'key' => $tid]));
            }
            // 立即转入
            dispatch_now(new TaobaoTradeBatchTransferJob(['tids' => [$tid], 'key' => $tid]));
        }

        // 标识，以免嵌套循环
        $this->content['from_adaptor_job'] = 1;

        $qimenApi = new AddressSelfModifyApi();
        $result = $qimenApi->execute($this->content);
        if (!$result['status']) {
            $this->release($this->delay);
        }
    }

    /**
     * 获取分配给这个任务的标签
     *
     * @return array
     */
    public function tags()
    {
        return ['delay_modify_address'];
    }
}