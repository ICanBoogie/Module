<?php

namespace ICanBoogie\Module;

use ICanBoogie;
use ICanBoogie\Application;
use ICanBoogie\Application\ClearCacheEvent;
use ICanBoogie\Binding\Event\ConfigBuilder;

return fn(ConfigBuilder $config) => $config
	->attach_to(Application::class, ClearCacheEvent::class, [ Hooks::class, 'on_app_clear_cache' ]);
	/* @deprecated */
//	BeforeSynthesizeRoutesEvent::class => [ Hooks::class, 'before_synthesize_routes' ]
	/* @deprecated */
//	ICanBoogie\HTTP\RequestDispatcher::class . '::alter' => $hooks . 'on_alter_request_dispatcher',
