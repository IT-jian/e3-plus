<?php


namespace App\Console\Commands;


use Illuminate\Console\Command;

class QueueWorkerConfigReloadCommand extends Command
{
    protected $name = 'adaptor:supervisor:reload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'supervisor 更新 supervisor';

    public function handle()
    {
        shell_exec("sudo supervisorctl reread");
        shell_exec("sudo supervisorctl update");

        shell_exec("supervisorctl reread");
        shell_exec("supervisorctl update");
    }
}