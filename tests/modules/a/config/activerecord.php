<?php

use ICanBoogie\ActiveRecord\Schema;
use ICanBoogie\ActiveRecord\SchemaColumn;
use ICanBoogie\Binding\ActiveRecord\ConfigBuilder;

return fn(ConfigBuilder $config) => $config
    ->add_model(
        id: 'a',
        schema: new Schema([
            'id' => SchemaColumn::serial(),
        ])
    );
