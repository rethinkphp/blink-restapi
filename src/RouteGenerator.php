<?php

namespace blink\restapi;

use blink\core\BaseObject;

/**
 * Class RouteGenerator
 *
 * @package blink\restapi
 */
class RouteGenerator extends BaseObject
{
    /**
     * Generate routes for given namespace and api path.
     *
     * @param $namespace
     * @param $path
     * @return array
     */
    public function generate($namespace, $path)
    {
        $iter = new \RecursiveDirectoryIterator($path);
        $path = realpath($path);

        $routes = [];
        /** @var \SplFileInfo $item */
        foreach (new \RecursiveIteratorIterator($iter) as $item) {
            if (in_array($item->getFilename(), ['.', '..'])) {
                continue;
            }

            $group = substr($item->getPath(), strlen($path) + 1);
            
            if ($group) {
                $class = '\\' . $namespace . '\\' . str_replace('/', '\\', $group) . '\\' .  $item->getBasename('.php');
            } else {
                $class = '\\' . $namespace . '\\' .  $item->getBasename('.php');
            }

            // TODO check this verb and path

            $routes[] = [$class::$verb, $class::$path, $class . '@run'];
        }

        return $routes;
    }
}
