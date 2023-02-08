<?php

namespace ICanBoogie\Binding\Module;

use ICanBoogie\ConfigProvider;
use ICanBoogie\Module\ModuleCollection;

final class Factory
{
    static public function build_config(ConfigProvider $provider): Config
    {
        return $provider->config_for_class(Config::class);
    }

    static public function build_modules(Config $config): ModuleCollection
    {
        return new ModuleCollection($config->descriptors);
    }
}
