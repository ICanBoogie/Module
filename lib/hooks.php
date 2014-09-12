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
use ICanBoogie\Module;

class Hooks
{
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
}