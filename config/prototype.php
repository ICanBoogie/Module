<?php

namespace ICanBoogie\Module;

use ICanBoogie;
use ICanBoogie\Binding\Module\PrototypeCallbacks;

$p = PrototypeCallbacks::class . '::';

/**
 * @uses PrototypeCallbacks::get_modules()
 * @uses PrototypeCallbacks::get_models()
 * @uses PrototypeCallbacks::controller_get_module()
 * @uses PrototypeCallbacks::controller_get_model()
 * @uses PrototypeCallbacks::controller_lazy_get_records_fetcher()
 * @uses PrototypeCallbacks::controller_fetch_records()
 * @uses PrototypeCallbacks::controller_fetch_record()
 */
return [

	ICanBoogie\Application::class . '::lazy_get_modules' => $p . 'get_modules',
	ICanBoogie\Application::class . '::lazy_get_models' => $p . 'get_models',
	ICanBoogie\Routing\ControllerAbstract::class . '::lazy_get_module' => $p . 'controller_get_module',
	ICanBoogie\Routing\ControllerAbstract::class . '::get_model' => $p . 'controller_get_model',
	ICanBoogie\Routing\ControllerAbstract::class . '::lazy_get_records_fetcher' => $p . 'controller_lazy_get_records_fetcher',
	ICanBoogie\Routing\ControllerAbstract::class . '::fetch_records' => $p . 'controller_fetch_records',
	ICanBoogie\Routing\ControllerAbstract::class . '::fetch_record' => $p . 'controller_fetch_record'

];
