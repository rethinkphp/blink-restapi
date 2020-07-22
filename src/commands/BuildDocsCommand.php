<?php

namespace blink\restapi\commands;

use blink\restapi\Manager;
use blink\restapi\RouteGenerator;
use blink\support\Json;
use rethink\typedphp\DocGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use blink\core\InvalidConfigException;

/**
 * Class BuildDocsCommand
 *
 * @package blink\restapi
 */
class BuildDocsCommand extends \blink\core\console\Command
{
    public $name = 'restapi:build-docs';
    public $description = 'Generate API docs from API classes';

    public function configure()
    {
        $this->addArgument('dst', InputArgument::REQUIRED, 'The path to generate into.');
        $this->addOption('ver', null, InputOption::VALUE_REQUIRED, 'The openapi version', '');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $dst = $input->getArgument('dst');
        $ver = $input->getOption('ver');
        
        /** @var Manager $manager */
        $manager = $this->blink->get('restapi');

        $manager->generateDocs($dst, $ver);
    }
}
