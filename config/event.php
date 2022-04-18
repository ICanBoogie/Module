<?php

namespace ICanBoogie\Module;

use ICanBoogie;
use ICanBoogie\Application;
use ICanBoogie\Application\ClearCacheEvent;
use ICanBoogie\Binding\Routing\BeforeSynthesizeRoutesEvent;

$hooks = Hooks::class . '::';

return [

	ClearCacheEvent::qualify(Application::class) => $hooks . 'on_app_clear_cache',
	/* @deprecated */
//	ICanBoogie\View\View::class . '::alter' => $hooks . 'on_view_alter',
	/* @deprecated */
//	ICanBoogie\HTTP\RequestDispatcher::class . '::alter' => $hooks . 'on_alter_request_dispatcher',

	BeforeSynthesizeRoutesEvent::TYPE => $hooks . 'before_synthesize_routes'

];
