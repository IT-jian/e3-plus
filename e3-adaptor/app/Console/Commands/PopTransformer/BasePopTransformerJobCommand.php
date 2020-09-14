<?php


namespace App\Console\Commands\PopTransformer;


use App\Jobs\DingTalkNoticeTextSendJob;
use App\Models\Sys\Shop;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BasePopTransformerJobCommand extends Command
{
    protected $defaultFromHours = 2;
    protected $defaultToHours = 1;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将一小时之前未格式化的数据，重新产生格式化JOB';

    // 是否按照店铺方式处理
    protected $popByShop = false;

    public function handle()
    {
        if ($this->hasOption('from') && !empty($this->option('from'))) {
            $from = Carbon::now()->subHours($this->option('from'))->toDateTimeString();
        } else {
            $from = Carbon::now()->subHours(2)->toDateTimeString();
        }

        if ($this->hasOption('to') && !empty($this->option('to'))) {
            $to = Carbon::now()->subHours($this->option('to'))->toDateTimeString();
        } else {
            $to = Carbon::now()->subMinutes(10)->toDateTimeString();
        }
        $this->info(sprintf("from:%s, %s", $this->option('from'), $from));
        $this->info(sprintf("to:%s, %s", $this->option('to'), $to));
        if ($this->popByShop) {
            $shops = Shop::all();
            if (empty($shops)) {
                return true;
            }
            foreach ($shops->toArray() as $shop) {
                $this->popJob($from, $to, $shop);
            }
        } else {
            $this->popJob($from, $to);
        }

        return true;
    }

    public function popJob($from, $to, $shop = [])
    {

    }

    public function sendNotice($msg)
    {
        $params = ['message' => $this->name . $msg];
        dispatch(new DingTalkNoticeTextSendJob($params));
    }
}
