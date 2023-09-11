<?php

use ICanBoogie\ActiveRecord\Config;
use ICanBoogie\ActiveRecord\SchemaBuilder;
use ICanBoogie\Binding\ActiveRecord\ConfigBuilder;
use Test\ICanBoogie\Acme\Article;
use Test\ICanBoogie\Acme\Node;

return fn(ConfigBuilder $config) => $config
    ->add_connection(Config::DEFAULT_CONNECTION_ID, 'sqlite::memory:')
    ->add_model(
        activerecord_class: Node::class,
        schema_builder: fn(SchemaBuilder $builder) => $builder
            ->add_serial('nid', primary: true)
            ->add_character('title', size: 80)
    )
    ->add_model(
        activerecord_class: Article::class,
        schema_builder: fn(SchemaBuilder $builder) => $builder
            ->add_date('date')
    );
