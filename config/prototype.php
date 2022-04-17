<?php

namespace ICanBoogie\Module;

use ICanBoogie;

$hooks = Hooks::class . '::';

return [

	ICanBoogie\Application::class . '::lazy_get_modules' => $hooks . 'get_modules',
	ICanBoogie\Application::class . '::lazy_get_models' => $hooks . 'get_models',
	ICanBoogie\Routing\ControllerAbstract::class . '::lazy_get_module' => $hooks . 'controller_get_module',
	ICanBoogie\Routing\ControllerAbstract::class . '::get_model' => $hooks . 'controller_get_model',
	ICanBoogie\Routing\ControllerAbstract::class . '::lazy_get_records_fetcher' => $hooks . 'controller_lazy_get_records_fetcher',
	ICanBoogie\Routing\ControllerAbstract::class . '::fetch_records' => $hooks . 'controller_fetch_records',
	ICanBoogie\Routing\ControllerAbstract::class . '::fetch_record' => $hooks . 'controller_fetch_record'

];
