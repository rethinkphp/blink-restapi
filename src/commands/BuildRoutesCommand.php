<?php

namespace blink\restapi\commands;

use blink\core\InvalidConfigException;
use blink\restapi\RouteGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\VarExporter\VarExporter;

/**
 * Class BuildRoutesCommand
 *
 * @package blink\restapi
 */
class BuildRoutesCommand extends \blink\core\console\Command
{
    public $name = 'restapi:build-routes';
    public $description = 'Generate route configurations from API classes';

    public $apiNamespace;
    public $apiPath;
    public $routePath;

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->apiNamespace || !$this->apiPath || !$this->routePath) {
            throw new InvalidConfigException('The configuration: apiNamespace, apiPath, routePath are not configured');
        }

        $generator = new RouteGenerator();
        $routes = $generator->generate($this->apiNamespace, app()->root . '/' . $this->apiPath);

        $this->writeRoutes(app()->root . '/' . $this->routePath, $routes);
    }

    protected function writeRoutes($path, array $routes)
    {
        $content = VarExporter::export($routes);
        $content = <<<ROUTES
<?php
/**
 * This file is generated automatically, DO NOT change it!!!
 */
return $content;
ROUTES;

        file_put_contents($path, $content);
    }
}
