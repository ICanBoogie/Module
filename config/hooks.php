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

	]

];
