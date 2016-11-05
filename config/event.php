<?php

namespace ICanBoogie\Module;

use ICanBoogie;

$hooks = Hooks::class . '::';

return [

	ICanBoogie\Application::class . '::configure' => $hooks . 'on_app_configure',
	ICanBoogie\Application::class . '::boot' => $hooks . 'on_app_boot',
	ICanBoogie\Application::class . '::clear_cache' => $hooks . 'on_app_clear_cache',
	ICanBoogie\Render\BasicTemplateResolver::class . '::alter' => $hooks . 'on_template_resolver_alter',
	ICanBoogie\View\View::class . '::alter' => $hooks . 'on_view_alter',
	ICanBoogie\HTTP\RequestDispatcher::class . '::alter' => $hooks . 'on_alter_request_dispatcher',

	'routing.synthesize_routes:before' => $hooks . 'before_synthesize_routes'

];
