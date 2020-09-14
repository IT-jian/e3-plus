<?php


namespace PlatformAdaptor\Generator\Commands;


use PlatformAdaptor\Generator\Commands\BaseCommand;
use PlatformAdaptor\Generator\Common\CommandData;

class GeneratorCommand extends BaseCommand
{
    protected $name = 'adaptor:admin';

    protected $description = 'Create a full CRUD API for given model';

    public function __construct()
    {
        parent::__construct();

        $this->commandData = new CommandData($this, CommandData::$COMMAND_TYPE_API);
    }

    public function handle()
    {
        parent::handle();

        $this->generateCommonItems();
        $this->generateApiItems();

        $this->performPostActions();
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