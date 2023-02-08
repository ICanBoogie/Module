<?php

namespace Test\ICanBoogie\Module\ModulesTest\ModuleD;

use ICanBoogie\Binding\Module\ConfigBuilder;

return fn(ConfigBuilder $config) => $config
    ->add_module(
        id: 'd',
        class: Module::class,
        require: [ 'b' ]
    );
