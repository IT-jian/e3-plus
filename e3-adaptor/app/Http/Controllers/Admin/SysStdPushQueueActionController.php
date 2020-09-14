<?php


namespace App\Http\Controllers\Admin;


use App\Facades\HubClient;
use App\Models\SysStdPushConfig;
use App\Models\SysStdPushQueue;
use App\Services\Hub\Jobs\SysStdPushBatchJob;
use Illuminate\Http\Request;

class SysStdPushQueueActionController extends Controller
{
    protected function getRequest($queue)
    {
        $method = $queue->method;
        $content = $queue->bis_id;
        $request = HubClient::hub($this->getProxy($queue))->resolveRequestClass($method);

        return $request->setContent($content);
    }

    protected function getProxy($queue)
    {
        $method = $queue->method;
        $pushConfig = (new SysStdPushConfig())->methodMapCache($method);

        return !empty($pushConfig['proxy']) ? $pushConfig['proxy'] : null;
    }

    /**
     * 推送
     *
     * @param $id
     * @return mixed
     *
     * @author linqihai
     * @since 2020/2/26 16:25
     */
    public function push($id)
    {
        $queue = SysStdPushQueue::whereIn('status', [0, 2])->lock()->findOrFail($id);
        $queue->status = 3;
        $queue->save();

        $request = $this->getRequest($queue);
        $result = HubClient::hub($this->getProxy($queue))->execute($request);

        $queue->status = 1 == $result['status'] ? 1 : 2;
        $queue->save();

        if (!$result['status']) {
            return $this->failed($result['message']);
        }

        return $this->respond($queue);
    }

    public function pushFormat($id)
    {
        $queue = SysStdPushQueue::findOrFail($id);
        $request = $this->getRequest($queue);
        $result = $request->getBody();

        return $this->respond($result);
    }

    /**
     * 批量处理
     *
     * @param Request $request
     * @return mixed
     *
     * @author linqihai
     * @since 2020/2/26 16:40
     */
    public function pushBatch(Request $request)
    {
        $queueMethodMap = [];
        $ids = $request->ids;
        $queues = SysStdPushQueue::whereIn('status', [0, 2])->lock()->find($ids);
        if (!$queues->isEmpty()) {
            foreach ($queues as $queue) {
                $queueMethodMap[$queue['method']][] = $queue['id'];
            }
            foreach ($queueMethodMap as $method => $queueIds) {
                $config = SysStdPushConfig::where('method', $method)->firstOrFail()->toArray();
                foreach (array_chunk($queueIds, 50) as $chunk) {
                    SysStdPushQueue::whereIn('id', $chunk)->update(['status' => 3]);
                    $force = true;
                    dispatch((new SysStdPushBatchJob($chunk, $config, 'manual force push', $force))->tries($config['tries'])->delay($config['delay'])->onQueue('default'));
                }
            }
        }

        $message = "Total requests " . $queues->count() . "，Job Running On Queue....";

        return $this->message($message);
    }
}
