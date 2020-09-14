<?php

namespace PlatformAdaptor\Generator\Tests;

use PlatformAdaptor\Generator\GeneratorsServiceProvider;
use PHPUnit\Framework\TestCase as PHPUnit;

use \Illuminate\Support\Facades\Artisan;
use \Illuminate\Filesystem\Filesystem;


class CommandTest extends PHPUnit
{
    protected $app;
    protected $filesystem;
    protected $folders;
    protected $files;

    public function setUp()
    {
        parent::setUp();

        $this->prepareFilesystem();

        $this->createApplication();

        $this->mountFolderStructure();
    }

    public function tearDown()
    {
        $this->cleanFilesystem();
    }

    public function createApplication()
    {
        $this->app = require __DIR__ . '/../vendor/laravel/laravel/bootstrap/app.php';

        $this->app->register('PlatformAdaptor\Generator\GeneratorsServiceProvider');

        $this->app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    }

    public function prepareFilesystem()
    {
        $this->filesystem = new Filesystem();

        $this->folders =
            [
                'app/Http/Controller',
            ];

        $this->files =
            [
//                './database/factories/UserFactory.php',
//                './database/seeds/DatabaseSeeder.php',
//                './app/Models/Model.php',
//                './app/Providers/AuthServiceProvider.php',
//                './app/Providers/AppServiceProvider.php',
//                './routes/web.php',
            ];

    }

    public function mountFolderStructure()
    {
        foreach ($this->folders as $folder) {
            $this->filesystem->makeDirectory($folder, 0777, true, true);
        }


        foreach ($this->files as $file) {
            $this->filesystem->put($file, '<?php');
        }
    }

    public function cleanFilesystem()
    {
        foreach ($this->folders as $folder) {
            $this->filesystem->deleteDirectory(explode("/", $folder)[0]);
        }
    }

    public function testExecuteCommand()
    {
        $output = '';
        $result = Artisan::call('adaptor:requests', [
            'model'        => 'Test',
            '--fieldsFile' => 'test.json',
        ], $output);
        print_r($result);
        var_dump($output);exit;
        $this->assertFileExists('./app/Requests/api/CreateTestApiRequest.php');
    }
}