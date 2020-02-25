<?php

namespace blink\restapi;

use blink\core\BaseObject;
use rethink\typedphp\ApiInterface;
use ReflectionClass;

/**
 * Class RouteGenerator
 *
 * @package blink\restapi
 */
class RouteGenerator extends BaseObject
{
    protected function requireFiles(string $path)
    {
        $iter = new \RecursiveDirectoryIterator($path);

        /** @var \SplFileInfo $item */
        foreach (new \RecursiveIteratorIterator($iter) as $item) {
            if (in_array($item->getFilename(), ['.', '..'])) {
                continue;
            }

            if ($item->getExtension() === 'php') {
                require_once $item->getRealPath();
            }
        }
    }

    /**
     * Generate routes for given namespace and api path.
     *
     * @param $paths
     * @return array
     */
    public function generate($paths)
    {
        array_walk($paths, [$this, 'requireFiles']);

        $classes = get_declared_classes();

        $routes = [];

        foreach ($classes as $class) {
            if (! is_subclass_of($class, ApiInterface::class)) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($class);
                if ($reflection->isAbstract()) {
                    continue;
                }
            } catch (\ReflectionException $e) {
                // this should never happen
                continue;
            }

            // TODO check this verb and path
            $routes[] = [$class::$verb, $class::$path, '\\'. $class . '@run'];
        }

        return $routes;
    }
}
