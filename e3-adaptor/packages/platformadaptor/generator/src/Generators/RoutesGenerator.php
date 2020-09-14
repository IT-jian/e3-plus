<?php


namespace PlatformAdaptor\Generator\Generators;


use Illuminate\Support\Str;
use PlatformAdaptor\Generator\Common\CommandData;
use PlatformAdaptor\Generator\Generators\BaseGenerator;

class RoutesGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;
    /** @var string */
    private $path;
    /** @var string */
    private $routeContents;
    /** @var string */
    private $routesTemplate;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathRoutes;
        $this->routeContents = file_get_contents($this->path);
        if (!empty($this->commandData->config->prefixes['route'])) {
            $routesTemplate = get_template('routes.prefix_routes');
        } else {
            $routesTemplate = get_template('routes.routes');
        }
        $this->routesTemplate = fill_template($this->commandData->dynamicVars, $routesTemplate);
    }

    public function generate()
    {
        $this->routeContents .= "\n\n" . $this->routesTemplate;
        file_put_contents($this->path, $this->routeContents);
        $this->commandData->commandComment("\n" . $this->commandData->config->mCamelPlural . ' routes added.');
    }

    public function rollback()
    {
        if (Str::contains($this->routeContents, $this->routesTemplate)) {
            $this->routeContents = str_replace($this->routesTemplate, '', $this->routeContents);
            file_put_contents($this->path, $this->routeContents);
            $this->commandData->commandComment('routes deleted');
        }
    }
}