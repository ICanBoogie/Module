<?php

namespace Test\ICanBoogie\Module\ModulesTest\Sample;

use ICanBoogie\Binding\Module\ConfigBuilder;

return fn(ConfigBuilder $config) => $config
    ->add_module('sample', Module::class);
