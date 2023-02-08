<?php

namespace Test\ICanBoogie\Module\ModulesTest\ModuleB;

use ICanBoogie\Binding\Module\ConfigBuilder;

return fn(ConfigBuilder $config) => $config
    ->add_module(
        id: 'b',
        class: Module::class,
        parent: 'a'
    );
