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

class Hooks
{
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
	 * @param Core $core
	 */
	static public function on_core_boot(Core\BootEvent $event, Core $core)
	{
		$modules = $core->modules;
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
			$core->configs->add($modules->config_paths, -10);
		}

		#
		# Revoke prototypes and events.
		#

		Prototype::configure($core->configs['prototypes']);

		unset($core->events);
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

		foreach (Core::get()->modules->descriptors as $module_id => $descriptor)
		{
			$module_roots[$descriptor[Module::T_PATH]] = $module_id;
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

	/*
	 * Prototypes
	 */

	/**
	 * Return the {@link Modules} instance used to manage the modules attached to the _core_.
	 *
	 * @param Core $core
	 *
	 * @return Modules The modules provider.
	 */
	static public function get_modules(Core $core)
	{
		$config = $core->config;

		return new Modules($config['module-path'], $config['cache modules'] ? $core->vars : null);
	}

	/**
	 * Returns the {@link Models} instance used to obtain the models defined by the modules.
	 *
	 * @return Models The models accessor.
	 */
	static public function get_models(Core $core)
	{
		return new Models($core->connections, [], $core->modules);
	}
}