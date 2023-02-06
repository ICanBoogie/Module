<?php

namespace ICanBoogie\Module;

use ICanBoogie;
use ICanBoogie\Application;
use ICanBoogie\Binding\Module\Hooks;
use ICanBoogie\Binding\Prototype\ConfigBuilder;

return fn(ConfigBuilder $config) => $config
    ->bind(Application::class, 'get_modules', [ Hooks::class, 'get_modules' ]);
