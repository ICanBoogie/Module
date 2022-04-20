<?php

namespace ICanBoogie\Module;

use ICanBoogie;
use ICanBoogie\Application;
use ICanBoogie\Application\ClearCacheEvent;
use ICanBoogie\Binding\Routing\BeforeSynthesizeRoutesEvent;

return [

	ClearCacheEvent::for(Application::class) => [ Hooks::class, 'on_app_clear_cache' ],
	BeforeSynthesizeRoutesEvent::class => [ Hooks::class, 'before_synthesize_routes' ]

	/* @deprecated */
//	ICanBoogie\View\View::class . '::alter' => $hooks . 'on_view_alter',
	/* @deprecated */
//	ICanBoogie\HTTP\RequestDispatcher::class . '::alter' => $hooks . 'on_alter_request_dispatcher',

];
