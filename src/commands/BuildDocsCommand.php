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
        $this->addOption('api-path', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Speficy the api path', []);
        $this->addOption('exclude-api-path', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Speficy the api path to exclude', []);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $dst = $input->getArgument('dst');
        $ver = $input->getOption('ver');
        /** @var array $apiPaths */
        $apiPaths = $input->getOption('api-path');
        /** @var array $excludeApiPaths */
        $excludeApiPaths = $input->getOption('exclude-api-path');
        
        /** @var Manager $manager */
        $manager = $this->blink->get('restapi');
        if (! empty($apiPaths)) {
            $manager->apiPaths = $apiPaths;
        } 

        if (! empty($excludeApiPaths)) {
            $manager->apiPaths = array_diff($manager->apiPaths, $excludeApiPaths);
        }

        $manager->generateDocs($dst, $ver);

        return 0;
    }
}
