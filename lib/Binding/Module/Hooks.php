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

use ICanBoogie\Application;
use ICanBoogie\Module\Autoconfig\ModuleAutoconfig;
use ICanBoogie\Module\ModuleCollection;

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
}
