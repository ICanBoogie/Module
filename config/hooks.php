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
		'ICanBoogie\Core::lazy_get_models' => $hooks . 'get_models'

	]

];