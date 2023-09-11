<?php

use ICanBoogie\ActiveRecord\SchemaBuilder;
use ICanBoogie\Binding\ActiveRecord\ConfigBuilder;
use modules\a\lib\ArA;

return fn(ConfigBuilder $config) => $config
    ->add_model(
        activerecord_class: ArA::class,
        schema_builder: fn(SchemaBuilder $builder) => $builder
            ->add_serial('id', primary: true)
    );
