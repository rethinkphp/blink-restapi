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

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->blink->get('restapi')->generateRoutes();
    }
}
