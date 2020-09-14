<?php


namespace PlatformAdaptor\Generator\Commands\Common;


use PlatformAdaptor\Generator\Commands\BaseCommand;
use PlatformAdaptor\Generator\Common\CommandData;
use PlatformAdaptor\Generator\Generators\ControllerGenerator;

class ControllerGeneratorCommand extends BaseCommand
{
    protected $name = 'adaptor:controller';

    protected $description = 'Create an controller command';

    public function __construct()
    {
        parent::__construct();

        $this->commandData = new CommandData($this, CommandData::$COMMAND_TYPE_API);
    }

    public function handle()
    {
        parent::handle();

        $controllerGenerator = new ControllerGenerator($this->commandData);
        $controllerGenerator->generate();

        // 执行后续动作，比如执行migrate, 执行composer dump-autoload 等命令
        $this->performPostActions();
    }

    public function getOptions()
    {
        return array_merge(parent::getOptions(), []);
    }

    protected function getArguments()
    {
        return array_merge(parent::getArguments(), []);
    }

}