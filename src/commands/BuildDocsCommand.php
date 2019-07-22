<?php

namespace blink\restapi\commands;

use blink\restapi\RouteGenerator;
use blink\support\Json;
use rethink\typedphp\DocGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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

    public $apiNamespace;
    public $apiPath;
    public $docsTemplatePath;
    public $typeParserFactory;

    public function configure()
    {
        $this->addArgument('dst', InputArgument::REQUIRED, 'The path to generate into.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

        if (!$this->apiNamespace || !$this->apiPath || !$this->docsTemplatePath) {
            throw new InvalidConfigException('The configuration: apiNamespace, apiPath, docsTemplatePath are not configured');
        }

        $apiClasses = $this->getApiClasses();

        $docs = $this->buildDocs($apiClasses);

        $this->writeDocs($docs, $input->getArgument('dst'));
    }

    protected function getApiClasses()
    {
        $generator = new RouteGenerator();
        $routes = $generator->generate($this->apiNamespace, app()->root . '/' . $this->apiPath);

        return array_map(function ($route) {
            return explode('@', $route[2])[0];
        }, $routes);
    }


    protected function buildDocs($apiClasses)
    {
        $parser = null;
        if ($this->typeParserFactory) {
            $parser = ($this->typeParserFactory)();
        }

        $generator = new DocGenerator($apiClasses, $parser);
        $segments = $generator->generate();

        $docs = Json::decode(file_get_contents($this->docsTemplatePath));

        $docs['components']['schemas'] = $segments['schemas'];
        $docs['paths'] = $segments['paths'];

        return $docs;
    }


    protected function writeDocs(array $docs, $path)
    {
        file_put_contents(
            $path,
            Json::encode($docs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
