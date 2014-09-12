<?php

namespace ICanBoogie\Module;

$hooks = __NAMESPACE__ . '\Hooks::';

return [

	'events' => [

		'routing.collect_routes:before' => $hooks . 'before_routing_collect_routes'

	]

];