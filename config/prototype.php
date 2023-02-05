<?php

namespace ICanBoogie\Module;

use ICanBoogie;
use ICanBoogie\Application;
use ICanBoogie\Binding\Module\Hooks;
use ICanBoogie\Binding\Prototype\ConfigBuilder;
use ICanBoogie\Routing\ControllerAbstract;

return fn(ConfigBuilder $config) => $config
	->bind(Application::class, 'get_modules', [ Hooks::class, 'get_modules' ])
	->bind(ControllerAbstract::class, 'lazy_get_module', [ Hooks::class, 'controller_get_module' ])
	->bind(ControllerAbstract::class, 'get_model', [ Hooks::class, 'controller_get_model' ])
	->bind(ControllerAbstract::class, 'lazy_get_records_fetcher', [ Hooks::class, 'controller_lazy_get_records_fetcher' ])
	->bind(ControllerAbstract::class, 'fetch_records', [ Hooks::class, 'controller_fetch_records' ])
	->bind(ControllerAbstract::class, 'fetch_record', [ Hooks::class, 'controller_fetch_record' ]);
