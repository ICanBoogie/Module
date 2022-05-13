<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Binding\Module;

use ICanBoogie\ActiveRecord;
use ICanBoogie\Application;
use ICanBoogie\Facets\Fetcher;
use ICanBoogie\Facets\Fetcher\BasicFetcher;
use ICanBoogie\Facets\RecordCollection;
use ICanBoogie\Module;
use ICanBoogie\Module\Autoconfig\ModuleAutoconfig;
use ICanBoogie\Module\ModuleCollection;
use ICanBoogie\Routing\ControllerAbstract;
use ICanBoogie\Routing\Route;

final class Hooks
{
	static public function get_modules(Application $app): ModuleCollection
	{
		static $modules;

		return $modules ??= self::build_modules($app);
	}

	static private function build_modules(Application $app): ModuleCollection
	{
		return new ModuleCollection(
			$app->auto_config[ModuleAutoconfig::MODULES],
			$app->config->cache_modules ? $app->vars : null
		);
	}

	/**
	 * Return the {@link Module} instance associated with the route handled by the controller.
	 *
	 * @param ControllerAbstract|ControllerBindings $controller
	 */
	static public function controller_get_module(ControllerAbstract $controller): Module
	{
		[ $module_id ] = explode(Route::ACTION_SEPARATOR, $controller->route->action);

		return $controller->app->modules[$module_id];
	}

	/**
	 * Return the primary model of the module associated with the route handled by the controller.
	 *
	 * @param ControllerAbstract|ControllerBindings $controller
	 *
	 * @return ActiveRecord\Model
	 *
	 * @see controller_get_module()
	 */
	static public function controller_get_model(ControllerAbstract $controller): ActiveRecord\Model
	{
		return $controller->module->model;
	}

	/**
	 * Return a record fetcher for the controller `model`.
	 *
	 * **Note:** The "icanboogie/facets" package is required.
	 *
	 * @param ControllerAbstract|ControllerBindings $controller
	 *
	 * @return BasicFetcher
	 */
	static public function controller_lazy_get_records_fetcher(ControllerAbstract $controller): BasicFetcher
	{
		return new BasicFetcher($controller->model);
	}

	/**
	 * Fetch records using the controller `records_fetcher`.
	 *
	 * @param ControllerAbstract|ControllerBindings $controller
	 * @param array $modifiers
	 *
	 * @return RecordCollection
	 */
	static public function controller_fetch_records(ControllerAbstract $controller, array $modifiers): RecordCollection
	{
		$fetcher = $controller->records_fetcher;

		return $fetcher($modifiers);
	}

	/**
	 * Fetch records using the controller `records_fetcher`.
	 *
	 * @param ControllerAbstract|ControllerBindings $controller
	 * @param array $modifiers
	 * @param Fetcher|null $fetcher Reference to a variable where the fetcher should be stored.
	 */
	static public function controller_fetch_record(
		ControllerAbstract $controller,
		array $modifiers,
		Fetcher &$fetcher = null
	): ?ActiveRecord {
		$fetcher = $controller->records_fetcher;
		$records = $fetcher($modifiers);

		if (!$records) {
			return null;
		}

		return $records->one;
	}
}
