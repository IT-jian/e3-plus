<?php


namespace App\Console\Commands;


use App\Jobs\DingTalkNoticeTextSendJob;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $name = 'oms:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'test oms';

    public function handle()
    {
        $params = [
            'message' => 'Test ====> Crontab Running <===== Test'
        ];
        dispatch(new DingTalkNoticeTextSendJob($params));
    }
}
