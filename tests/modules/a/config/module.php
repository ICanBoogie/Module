<?php

namespace Test\ICanBoogie\Module\ModulesTest\ModuleA;

use ICanBoogie\Binding\Module\ConfigBuilder;

use modules\a\lib\ArA;

use function dirname;

return fn(ConfigBuilder $config) => $config
    ->add_module(
        id: 'a',
        class: Module::class,
        models: [ ArA::class ],
        path: dirname(__DIR__)
    );
