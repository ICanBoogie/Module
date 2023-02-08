<?php

use ICanBoogie\ActiveRecord\Schema;
use ICanBoogie\ActiveRecord\SchemaColumn;
use ICanBoogie\Binding\ActiveRecord\Config;
use ICanBoogie\Binding\ActiveRecord\ConfigBuilder;

return fn(ConfigBuilder $config) => $config
    ->add_connection(Config::DEFAULT_CONNECTION_ID, 'sqlite::memory:')
    ->add_model(
        'nodes',
        new Schema([
            'nid' => SchemaColumn::serial(),
            'title' => SchemaColumn::varchar(80),
        ])
    )
    ->add_model(
        'contents',
        new Schema([
            'date' => SchemaColumn::datetime(),
        ]),
        extends: 'nodes',
    );
