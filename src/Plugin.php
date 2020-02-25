<?php

namespace blink\restapi;

use blink\core\Application;
use blink\core\PluginContract;

/**
 * Class Plugin
 *
 * @package blink\restapi
 */
class Plugin implements PluginContract
{
    public $apiPaths;
    public $templatePath;
    public $routePath;

    public $typeParserFactory;

    /**
     * @inheritDoc
     */
    public function install(Application $app)
    {
        $app->bind('restapi', [
            'class' => Manager::class,
            'apiPaths' => $this->apiPaths,
            'templatePath' => $this->templatePath,
            'routePath' => $this->routePath,
            'typeParserFactory' => $this->typeParserFactory,
        ]);
    }
}
