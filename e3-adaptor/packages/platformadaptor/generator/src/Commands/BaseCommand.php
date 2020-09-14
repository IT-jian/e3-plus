<?php


namespace PlatformAdaptor\Generator\Commands;


use PlatformAdaptor\Generator\Generators\VueJs\ElementUiGenerator;
use Illuminate\Console\Command;
use PlatformAdaptor\Generator\Common\CommandData;
use PlatformAdaptor\Generator\Generators\ControllerGenerator;
use PlatformAdaptor\Generator\Generators\RoutesGenerator;
use PlatformAdaptor\Generator\Generators\MigrationGenerator;
use PlatformAdaptor\Generator\Generators\ModelGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class BaseCommand extends Command
{
    /**
     * @var CommandData
     */
    public $commandData;

    public $composer;

    public function __construct()
    {
        parent::__construct();
        $this->composer = app()['composer'];
    }

    public function handle()
    {
        $this->commandData->modelName = $this->argument('model');
        $this->commandData->modelLabel = $this->option('label');
        $this->commandData->initCommandData();
        $this->commandData->getFields();
    }

    public function performPostActions($runMigration = false)
    {
        if ($runMigration) {
            if (!$this->isSkip('migration')) {
                if ($this->confirm("\nDo you want to migrate database? [y|N]", false)) {
                    $this->call('migrate');
                }
            }
        }

        if (!$this->isSkip('dump-autoload')) {
            $this->info('Generating autoload files');
            $this->composer->dumpOptimized();
        }
    }

    public function isSkip($skip)
    {
        if ($this->commandData->getOption('skip')) {
            return in_array($skip, (array) $this->commandData->getOption('skip'));
        }
        return false;
    }

    public function performPostActionsWithMigration()
    {
        $this->performPostActions(true);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            ['fieldsFile', null, InputOption::VALUE_REQUIRED, 'Fields input as json file'],
            ['label', null, InputOption::VALUE_REQUIRED, '中文名称'],
            ['tableName', null, InputOption::VALUE_REQUIRED, 'Table Name'],
            ['primary', null, InputOption::VALUE_REQUIRED, '自定义主键名称'],
            ['prefix', null, InputOption::VALUE_REQUIRED, '增加前缀'],
            ['skip', null, InputOption::VALUE_REQUIRED, 'Skip Specific Items to Generate (migration,model,controllers,repository,routes,tests,dump-autoload,permissions)'],
        ];
    }
    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'Singular Model name'],
        ];
    }

    public function generateCommonItems()
    {
        // migrate 内容
        if (!$this->isSkip('migration')) {
            $migrationsGenerator = new MigrationGenerator($this->commandData);
            $migrationsGenerator->generate();
        }
        // model
        if (!$this->isSkip('model')) {
            $modelGenerator = new ModelGenerator($this->commandData);
            $modelGenerator->generate();
        }

        if (!$this->isSkip('vue')) {
            $vueGenerator = new ElementUiGenerator($this->commandData);
            $vueGenerator->generate();
        }
    }

    public function generateApiItems()
    {
        if (!$this->isSkip('controllers')) {
            $controllerGenerator = new ControllerGenerator($this->commandData);
            $controllerGenerator->generate();
        }
        if (!$this->isSkip('routes')) {
            $routesGenerator = new RoutesGenerator($this->commandData);
            $routesGenerator->generate();
        }
    }

    /**
     * @param $fileName
     * @param string $prompt
     *
     * @return bool
     */
    protected function confirmOverwrite($fileName, $prompt = '')
    {
        $prompt = (empty($prompt))
            ? $fileName.' already exists. Do you want to overwrite it? [y|N]'
            : $prompt;
        return $this->confirm($prompt, false);
    }
}