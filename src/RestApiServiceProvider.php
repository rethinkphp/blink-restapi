<?php

namespace blink\restapi;

use blink\console\Application;
use blink\console\events\CommandRegistering;
use blink\di\Container;
use blink\di\ServiceProvider;
use blink\eventbus\EventBus;
use blink\restapi\commands\BuildDocsCommand;
use blink\restapi\commands\BuildRoutesCommand;

class RestApiServiceProvider extends ServiceProvider
{
    public function __construct(protected string $configPath)
    {
    }

    public function register(Container $container): void
    {
        /** @var EventBus $bus */
        $bus = $container->get(EventBus::class); 

        $bus->attach(CommandRegistering::class, function (CommandRegistering $event) {
            $event->app->registerCommand(BuildDocsCommand::class);
            $event->app->registerCommand(BuildRoutesCommand::class);
        });
        
        $container->bind('restapi', [
            'class' => Manager::class,
            ...require $this->configPath,
        ]);
    }
}
