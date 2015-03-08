<?php

namespace ICanBoogie\Module;

$hooks = __NAMESPACE__ . '\Hooks::';

return [

	'events' => [

		'ICanBoogie\Core::boot' => $hooks . 'on_core_boot',
		'ICanBoogie\Render\BasicTemplateResolver::alter' => $hooks . 'on_template_resolver_alter',
		'ICanBoogie\View\View::alter' => $hooks . 'on_view_alter',

		'routing.synthesize_routes:before' => $hooks . 'before_synthesize_routes'

	],

	'prototypes' => [

		'ICanBoogie\Core::lazy_get_modules' => $hooks . 'get_modules',
		'ICanBoogie\Core::lazy_get_models' => $hooks . 'get_models',
		'ICanBoogie\Routing\Controller::get_module' => $hooks . 'controller_get_module',
		'ICanBoogie\Routing\Controller::get_model' => $hooks . 'controller_get_model',
		'ICanBoogie\Routing\Controller::lazy_get_records_fetcher' => $hooks . 'controller_lazy_get_records_fetcher',
		'ICanBoogie\Routing\Controller::fetch_records' => $hooks . 'controller_fetch_records'

	]

];
