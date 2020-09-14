<?php


namespace PlatformAdaptor\Generator;


use PlatformAdaptor\Generator\Commands\Common\ControllerGeneratorCommand;
use PlatformAdaptor\Generator\Commands\Common\ElementUIGeneratorCommand;
use PlatformAdaptor\Generator\Commands\Common\MigrationGeneratorCommand;
use PlatformAdaptor\Generator\Commands\Common\ModelGeneratorCommand;
use PlatformAdaptor\Generator\Commands\GeneratorCommand;
use PlatformAdaptor\Generator\Commands\RollbackCommand;
use Illuminate\Support\ServiceProvider;

class GeneratorsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $configPath = __DIR__.'/../config/generator.php';
        $this->publishes([
             $configPath => config_path('generator.php'),
         ]);
    }

    public function register()
    {
        $this->app->singleton('adaptor:admin', function($app) {
            return new GeneratorCommand();
        });

        $this->app->singleton('adaptor:migration', function($app) {
            return new MigrationGeneratorCommand();
        });

        $this->app->singleton('adaptor:model', function($app) {
            return new ModelGeneratorCommand();
        });

        $this->app->singleton('adaptor:controller', function ($app) {
            return new ControllerGeneratorCommand();
        });

        $this->app->singleton('adaptor:rollback', function($app) {
            return new RollbackCommand();
        });

        $this->app->singleton('adaptor:vue', function($app) {
            return new ElementUIGeneratorCommand();
        });

        $this->commands([
            'adaptor:admin',
            'adaptor:vue',
            'adaptor:model',
            'adaptor:migration',
            'adaptor:controller',
            'adaptor:rollback',
        ]);
    }
}