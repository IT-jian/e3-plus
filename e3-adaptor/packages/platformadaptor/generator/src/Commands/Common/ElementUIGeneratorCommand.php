<?php


namespace PlatformAdaptor\Generator\Commands\Common;


use PlatformAdaptor\Generator\Commands\BaseCommand;
use PlatformAdaptor\Generator\Common\CommandData;
use PlatformAdaptor\Generator\Generators\VueJs\ElementUiGenerator;

class ElementUIGeneratorCommand extends BaseCommand
{
    protected $name = 'adaptor:vue';

    protected $description = 'Create element-ui view command';

    public function __construct()
    {
        parent::__construct();

        $this->commandData = new CommandData($this, CommandData::$COMMAND_TYPE_API);
    }

    public function handle()
    {
        parent::handle();

        $generator = new ElementUiGenerator($this->commandData);
        $generator->generate();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return array_merge(parent::getOptions(), []);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(), []);
    }
}