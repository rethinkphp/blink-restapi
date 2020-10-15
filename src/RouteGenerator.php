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

        return $this->sort($routes);
    }

    private function sort(array $routes)
    {
        usort($routes, function ($route1, $route2) {
            $parts1 = explode('/', $route1[1]);
            $parts2 = explode('/', $route2[1]);
            
            if (count($parts1) != count($parts2)) {
                return count($parts1) <=> count($parts2);
            }
            
            foreach ($parts1 as $i => $a) {
                $b = $parts2[$i];

                if ($a === $b) {
                    // noop
                } elseif ($a === null) {
                    return -1;
                } elseif ($b === null) {
                    return 1;
                } elseif (preg_match('/^{\w*?}$/', $a)) {
                    return 1;
                } else {
                    return $a <=> $b;
                }
            }
            
            return 0;
        });
        
        return $routes;
    }

}
