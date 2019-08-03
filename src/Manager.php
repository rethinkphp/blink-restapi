<?php

namespace blink\restapi;

use blink\core\BaseObject;
use blink\support\Json;
use rethink\typedphp\DocGenerator;
use blink\core\InvalidConfigException;
use Symfony\Component\VarExporter\VarExporter;

/**
 * Class Manager
 *
 * @package blink\restapi
 */
class Manager extends BaseObject
{
    public $apiNamespace;
    public $apiPath;
    public $templatePath;
    public $routePath;

    public $typeParserFactory;


    /**
     * Build the routes through the configured namespace and path.
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function buildRoutes()
    {
        if (!$this->apiNamespace || !$this->apiPath) {
            throw new InvalidConfigException('The configuration: apiNamespace, apiPath, routePath are not configured');
        }

        $generator = new RouteGenerator();


        return $generator->generate($this->apiNamespace, $this->normalizePath($this->apiPath));
    }

    /**
     * Build the routes and save it to the configured route path.
     *
     * @throws InvalidConfigException
     */
    public function generateRoutes()
    {
        if (!$this->routePath) {
            throw new InvalidConfigException('The configuration: routePath is not configured');
        }

        $routes = $this->buildRoutes($this->apiNamespace, $this->apiPath);

        $this->writeRoutes($this->routePath, $routes);
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

    protected function getApiClasses()
    {
        $routes = $this->buildRoutes();

        return array_map(function ($route) {
            return explode('@', $route[2])[0];
        }, $routes);
    }


    /**
     * Build the docs through the configurations.
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function buildDocs()
    {
        if (!$this->templatePath) {
            throw new InvalidConfigException('The configuration: templatePath is not configured');
        }

        $parser = null;
        if ($this->typeParserFactory) {
            $parser = ($this->typeParserFactory)();
        }

        $generator = new DocGenerator($this->getApiClasses(), $parser);
        $segments = $generator->generate();

        $content = file_get_contents($this->normalizePath($this->templatePath));

        $docs = Json::decode($content);

        $docs['components']['schemas'] = $segments['schemas'];
        $docs['paths'] = $segments['paths'];

        return $docs;
    }


    /**
     * Build the docs and save it to the specified path.
     *
     * @param string $path
     * @throws InvalidConfigException
     */
    public function generateDocs($path)
    {
        $docs = $this->buildDocs();

        file_put_contents(
            $path,
            Json::encode($docs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    protected function normalizePath($path)
    {
        if ($path[0] !== '/') {
            $path = app()->root . '/' . $path;
        }

        return $path;
    }
}
