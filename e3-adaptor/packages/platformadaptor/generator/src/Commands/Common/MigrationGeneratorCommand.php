<?php


namespace PlatformAdaptor\Generator\Commands\Common;

use PlatformAdaptor\Generator\Commands\BaseCommand;
use PlatformAdaptor\Generator\Common\CommandData;
use PlatformAdaptor\Generator\Generators\MigrationGenerator;

class MigrationGeneratorCommand extends BaseCommand
{
    protected $name = 'adaptor:migration';

    protected $description = 'Create migration command';

    public function __construct()
    {
        parent::__construct();

        $this->commandData = new CommandData($this, CommandData::$COMMAND_TYPE_API);
    }

    public function handle()
    {
        parent::handle();

        $migrationGenerator = new MigrationGenerator($this->commandData);
        $migrationGenerator->generate();

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