<?php

namespace Test\ICanBoogie\Module\ModulesTest\ModuleA;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\ActiveRecord\Schema;
use ICanBoogie\ActiveRecord\SchemaColumn;
use ICanBoogie\Module\Descriptor;

return [

	Descriptor::TITLE => 'A',
	Descriptor::NS => __NAMESPACE__,
	Descriptor::MODELS => [

		'primary' => [

			Model::SCHEMA => new Schema([

				'id' => SchemaColumn::serial(),

			])

		]

	]

];
