<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module;

use ICanBoogie\Core;
use ICanBoogie\HTTP\Request;
use ICanBoogie\I18n;
use ICanBoogie\Module;
use ICanBoogie\Prototype;
use ICanBoogie\Routing\Controller;
use ICanBoogie\View;

class Hooks
{
	/*
	 * Config
	 */

	/**
	 * Adds "modules" directories found in the app directories to `module-path`.
	 *
	 * @param array $autoconfig
	 */
	static public function filter_autoconfig(array &$autoconfig, $root)
	{
		$directories = \ICanBoogie\resolve_app_paths($root);

		foreach ($directories as $directory)
		{
			if (file_exists($directory . 'modules'))
			{
				$autoconfig['module-path'][] = $directory . 'modules';
			}
		}
	}

	/*
	 * Events
	 */

	/**
	 * Boot enabled modules.
	 *
	 * Before the modules are actually booted up, their index is used to alter the I18n load
	 * paths and the config paths.
	 *
	 * @param Core\BootEvent $event
	 * @param Core $app
	 */
	static public function on_core_boot(Core\BootEvent $event, Core $app)
	{
		$modules = $app->modules;
		$index = $modules->index;

		if (class_exists('ICanBoogie\I18n', true))
		{
			I18n::$load_paths = array_merge(I18n::$load_paths, $modules->locale_paths);
		}

		#
		# Add modules config paths to the configs path.
		#

		$modules_config_paths = $modules->config_paths;

		if ($modules_config_paths)
		{
			$app->configs->add($modules->config_paths, -10);
		}

		#
		# Revoke prototypes and events.
		#

		Prototype::configure($app->configs['prototypes']);

		unset($app->events);
	}

	/**
	 * Alter routes defined by modules by adding a `module` key that holds the identifier of the
	 * module that defines the route.
	 *
	 * @param \ICanBoogie\Routing\BeforeCollectRoutesEvent $event
	 */
	static public function before_routing_collect_routes(\ICanBoogie\Routing\BeforeCollectRoutesEvent $event)
	{
		$module_roots = [];

		foreach (\ICanBoogie\app()->modules->descriptors as $module_id => $descriptor)
		{
			$module_roots[$descriptor[Descriptor::PATH]] = $module_id;
		}

		foreach ($event->fragments as $module_root => &$fragment)
		{
			$module_root = dirname(dirname($module_root)) . DIRECTORY_SEPARATOR;

			if (empty($module_roots[$module_root]))
			{
				continue;
			}

			$module_id = $module_roots[$module_root];

			foreach ($fragment as $route_id => &$route)
			{
				$route += [

					'via' => Request::METHOD_ANY,
					'module' => $module_id

				];
			}
		}
	}

	/**
	 * If the view renders a module's route, the "template" directory of that module is added
	 * to the list of templates locations.
	 *
	 * @param View\AlterEvent $event
	 * @param View $target
	 */
	static public function on_view_alter(View\AlterEvent $event, View $target)
	{
		try
		{
			$module = $target->controller->module;
		}
		catch (PropertyNotDefined $e)
		{
			return;
		}

		$target->add_path($module->descriptor[Descriptor::PATH] . 'templates');
	}

	/*
	 * Prototypes
	 */

	/**
	 * Return the {@link Modules} instance used to manage the modules attached to the _core_.
	 *
	 * @param Core $app
	 *
	 * @return Modules The modules provider.
	 */
	static public function get_modules(Core $app)
	{
		$config = $app->config;

		return new Modules($config['module-path'], $config['cache modules'] ? $app->vars : null);
	}

	/**
	 * Returns the {@link Models} instance used to obtain the models defined by the modules.
	 *
	 * @return Models The models accessor.
	 */
	static public function get_models(Core $app)
	{
		return new Models($app->connections, [], $app->modules);
	}

	/**
	 * Return the {@link Module} instance associated with the route handled by the controller.
	 *
	 * @param Controller $controller
	 *
	 * @return Module
	 */
	static public function controller_get_module(Controller $controller)
	{
		$module_id = $controller->route->module;

		return $controller->app->modules[$module_id];
	}

	/**
	 * Return the primary model of the module associated with the route handled by the controller.
	 *
	 * @param Controller $controller
	 *
	 * @return \ICanBoogie\ActiveRecord\Model
	 *
	 * @see controller_get_module()
	 */
	static public function controller_get_model(Controller $controller)
	{
		return $controller->module->model;
	}
}
