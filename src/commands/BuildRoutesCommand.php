<?php

namespace blink\restapi\commands;

use blink\core\InvalidConfigException;
use blink\di\Container;
use blink\restapi\RouteGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\VarExporter\VarExporter;

/**
 * Class BuildRoutesCommand
 *
 * @package blink\restapi
 */
class BuildRoutesCommand extends \blink\console\Command
{
    public string $name = 'restapi:build-routes';
    public string $description = 'Generate route configurations from API classes';

    public function execute(InputInterface $input, OutputInterface $output)
    {
        Container::$global->get('restapi')->generateRoutes();

        return 0;
    }
}
