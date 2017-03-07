<?php

namespace ICanBoogie\Module\ModulesTest\ModuleA;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module\Descriptor;

return [

	Descriptor::TITLE => 'A',
	Descriptor::NS => __NAMESPACE__,
	Descriptor::MODELS => [

		'primary' => [

			Model::SCHEMA => [

				'id' => 'serial'

			]

		]

	]

];
