<?php

namespace Test\ICanBoogie\Module\ModulesTest\ModuleC;

use ICanBoogie\Binding\Module\ConfigBuilder;

return fn(ConfigBuilder $config) => $config
    ->add_module(
        id: 'c',
        class: Module::class,
        parent: 'b'
    );
