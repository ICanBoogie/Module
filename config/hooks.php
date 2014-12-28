<?php

namespace ICanBoogie\Module;

$hooks = __NAMESPACE__ . '\Hooks::';

return [

	'events' => [

		'ICanBoogie\Core::boot' => $hooks . 'on_core_boot',

		'routing.collect_routes:before' => $hooks . 'before_routing_collect_routes'

	],

	'prototypes' => [

		'ICanBoogie\Core::lazy_get_modules' => $hooks . 'get_modules',
		'ICanBoogie\Core::lazy_get_models' => $hooks . 'get_models',
		'ICanBoogie\Routing\Controller::get_module' => $hooks . 'controller_get_module',
		'ICanBoogie\Routing\Controller::get_model' => $hooks . 'controller_get_model'

	]

];
