<?php

namespace ICanBoogie\Module;

use ICanBoogie;
use ICanBoogie\Application;
use ICanBoogie\Binding\Module\Hooks;
use ICanBoogie\Routing\ControllerAbstract;

return [

	Application::class . '::lazy_get_modules' => [ Hooks::class, 'get_modules' ],
	Application::class . '::lazy_get_models' => [ Hooks::class, 'get_models' ],
	ControllerAbstract::class . '::lazy_get_module' => [ Hooks::class, 'controller_get_module' ],
	ControllerAbstract::class . '::get_model' => [ Hooks::class, 'controller_get_model' ],
	ControllerAbstract::class . '::lazy_get_records_fetcher' => [ Hooks::class, 'controller_lazy_get_records_fetcher' ],
	ControllerAbstract::class . '::fetch_records' => [ Hooks::class, 'controller_fetch_records' ],
	ControllerAbstract::class . '::fetch_record' => [ Hooks::class, 'controller_fetch_record' ],

];
