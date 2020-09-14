<?php


namespace App\Console\Commands;


use App\Models\QueueWorkerConfig;
use Illuminate\Console\Command;

class QueueWorkerConfigSyncCommand extends Command
{
    protected $name = 'adaptor:supervisor:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步数据库supervisor配置，同步更新服务器supervisor配置';

    public function handle()
    {
        $configMap = $this->getAllConfigs();
        $works = $this->getWorks();
        $existWorkerCode = [];
        $refresh = false;
        foreach ($works as $work) {
            if (isset($configMap[$work['code']])) {
                $modified = $configMap[$work['code']];
                if (strtotime($work['updated_at']) > $modified) {
                    $this->updateConfig($work);
                    $this->comment($work['code'] . 'updating');
                    $refresh = true;
                }
            } else {
                $this->createConfig($work);
                $this->comment($work['code'] .'creating');
                $refresh = true;
            }
            $existWorkerCode[] = $work['code'];
        }

        foreach ($configMap as $code => $config) {
            if (!in_array($code, $existWorkerCode)) {
                $this->deleteConfig($code);
                $this->comment($code .'deleting');
                $refresh = true;
            }
        }
        if ($refresh) {
            $this->comment('refreshing');
            $this->call(QueueWorkerConfigReloadCommand::class);
        }
    }

    public function storage()
    {
        return \Storage::disk('supervisor');
    }

    public function getWorks()
    {
        $works = QueueWorkerConfig::where('status', 1)->get([
                                                                'id',
                                                                'code',
                                                                'name',
                                                                'process_number',
                                                                'command',
                                                                'user',
                                                                'status',
                                                                'created_at',
                                                                'updated_at',
                                                            ]);

        return $works->toArray();
    }

    public function getAllConfigs()
    {
        $configMap = [];
        $configs = $this->storage()->files();
        foreach ($configs as $config) {
            $name = explode('.', $config)[0];
            $modified = $this->storage()->lastModified($config);
            $configMap[$name] = $modified;
        }

        return $configMap;
    }

    public function deleteConfig($name)
    {
        $this->storage()->delete($name . '.conf');
    }

    public function createConfig($work)
    {
        $name = $work['code'];
        $numprocs = $work['process_number'];
        $artisanCommand = $work['command'];
        $command = "php " . base_path('artisan') . ' ' .$artisanCommand;
        $user = $work['user'];
        $logPath = base_path("storage/logs/{$name}-queue-stdout.log");

        $template = $this->storage()->get('/stub/adaptor.stub');

        $template = str_replace('$WORKER_NAME$', $name, $template);
        $template = str_replace('$WORKER_NUMBER$', $numprocs, $template);
        $template = str_replace('$WORKER_COMMAND$', $command, $template);
        $template = str_replace('$WORKER_USER$', $user, $template);
        $template = str_replace('$WORKER_LOG$', $logPath, $template);
        $fileName = $name . '.conf';
        $this->storage()->put($fileName, $template);
    }

    public function updateConfig($work)
    {
        $name = $work['code'];
        $fileName = $name . '.conf';
        $fileNameBak = $fileName .'_bak';
        if ($this->storage()->exists($fileNameBak)) {
            $this->storage()->delete($fileNameBak);
        }
        $this->storage()->move($fileName, $fileNameBak);
        try {
            $this->createConfig($work);
        } catch (\Exception $e) {
            \Log::error('更新失败');
            $this->storage()->move($fileNameBak, $fileName);
        }
        $this->storage()->delete($fileNameBak);
    }
}