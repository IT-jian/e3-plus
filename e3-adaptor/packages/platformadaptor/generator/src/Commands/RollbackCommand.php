<?php


namespace PlatformAdaptor\Generator\Commands;


use PlatformAdaptor\Generator\Generators\ControllerGenerator;
use PlatformAdaptor\Generator\Generators\MigrationGenerator;
use PlatformAdaptor\Generator\Generators\ModelGenerator;
use PlatformAdaptor\Generator\Generators\RoutesGenerator;
use PlatformAdaptor\Generator\Generators\VueJs\ElementUiGenerator;
use Illuminate\Console\Command;
use PlatformAdaptor\Generator\Common\CommandData;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RollbackCommand extends Command
{
    public $commandData;

    protected $name = "adaptor:rollback";

    protected $description = 'Rollback a full CRUD API and Scaffold for given model';

    public $composer;

    public function __construct()
    {
        parent::__construct();

        $this->composer = app()['composer'];
    }

    public function handle()
    {
        if (!in_array($this->argument('type'), [
            CommandData::$COMMAND_TYPE_API,
            CommandData::$COMMAND_TYPE_SCAFFOLD
        ])) {
            $this->error('invalid rollback type');
        }

        $this->commandData = new CommandData($this, $this->argument('type'));
        $this->commandData->config->mName = $this->commandData->modelName = $this->argument('model');

        $this->commandData->config->init($this->commandData, ['tableName', 'prefix']);

        $controllerGenerator = new ControllerGenerator($this->commandData);
        $controllerGenerator->rollback();

        $migrationGenerator = new MigrationGenerator($this->commandData);
        $migrationGenerator->rollback();

        $apiRouteGenerator = new RoutesGenerator($this->commandData);
        $apiRouteGenerator->rollback();

        $modelGenerator = new ModelGenerator($this->commandData);
        $modelGenerator->rollback();

        $vueGenerator = new ElementUiGenerator($this->commandData);
        $vueGenerator->rollback();

        $this->info('Generating autoload files');
        $this->composer->dumpOptimized();
    }

    // 定义输入的选项
    public function getOptions()
    {
        return [
            ['tableName', null, InputOption::VALUE_REQUIRED, 'Table Name'],
            ['prefix', null, InputOption::VALUE_REQUIRED, 'Prefix for all files'],
        ];
    }

    // 定义输入的参数
    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'Singular Model name'],
            ['type', InputArgument::REQUIRED, 'Rollback type: (api / scaffold / scaffold_api)'],
        ];
    }
}