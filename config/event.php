<?php

namespace ICanBoogie\Module;

use ICanBoogie;

$hooks = Hooks::class . '::';

return [

	ICanBoogie\Core::class . '::configure' => $hooks . 'on_core_configure',
	ICanBoogie\Core::class . '::boot' => $hooks . 'on_core_boot',
	ICanBoogie\Render\BasicTemplateResolver::class . '::alter' => $hooks . 'on_template_resolver_alter',
	ICanBoogie\View\View::class . '::alter' => $hooks . 'on_view_alter',
	ICanBoogie\HTTP\RequestDispatcher::class . '::alter' => $hooks . 'on_alter_request_dispatcher',

	'routing.synthesize_routes:before' => $hooks . 'before_synthesize_routes'

];
