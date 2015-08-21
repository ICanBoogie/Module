<?php

namespace ICanBoogie\Module;

use ICanBoogie;

$hooks = Hooks::class . '::';

return [

	'events' => [

		ICanBoogie\Core::class . '::configure' => $hooks . 'on_core_configure',
		ICanBoogie\Core::class . '::boot' => $hooks . 'on_core_boot',
		ICanBoogie\Render\BasicTemplateResolver::class . '::alter' => $hooks . 'on_template_resolver_alter',
		ICanBoogie\View\View::class . '::alter' => $hooks . 'on_view_alter',

		'routing.synthesize_routes:before' => $hooks . 'before_synthesize_routes'

	],

	'prototypes' => [

		ICanBoogie\Core::class . '::lazy_get_modules' => $hooks . 'get_modules',
		ICanBoogie\Core::class . '::lazy_get_models' => $hooks . 'get_models',
		ICanBoogie\Routing\Controller::class . '::get_module' => $hooks . 'controller_get_module',
		ICanBoogie\Routing\Controller::class . '::get_model' => $hooks . 'controller_get_model',
		ICanBoogie\Routing\Controller::class . '::lazy_get_records_fetcher' => $hooks . 'controller_lazy_get_records_fetcher',
		ICanBoogie\Routing\Controller::class . '::fetch_records' => $hooks . 'controller_fetch_records',
		ICanBoogie\Routing\Controller::class . '::fetch_record' => $hooks . 'controller_fetch_record'

	]

];
