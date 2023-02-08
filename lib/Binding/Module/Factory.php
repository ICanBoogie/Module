<?php

namespace ICanBoogie\Binding\Module;

use ICanBoogie\Application;
use ICanBoogie\Module\Autoconfig\ModuleAutoconfig;
use ICanBoogie\Module\ModuleCollection;

final class Factory
{
    static public function build_modules(Application $app): ModuleCollection
    {
        return new ModuleCollection(
            $app->auto_config[ModuleAutoconfig::MODULES],
            $app->config->cache_modules ? $app->vars : null
        );
    }
}
