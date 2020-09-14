<?php


namespace App\Tasks;


use App\Models\HubApiLog;
use App\Models\HubClientLog;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Support\Facades\Log;

class TestLogTask extends Task
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        $table = $this->data['table'] ?? '';
        switch ($table) {
            case 'hub_api_log':
                HubApiLog::insert($this->data['logs']);
                break;
            case 'hub_client_log':
                HubClientLog::insert($this->data['logs']);
                break;
            default:
                Log::debug('test log', [$this->data]);
        }
    }
}