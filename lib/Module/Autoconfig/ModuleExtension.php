<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module\Autoconfig;

use ICanBoogie\Autoconfig\Autoconfig;
use ICanBoogie\Autoconfig\ExtensionAbstract;

/**
 * Autoconfig extension to handle modules, their config and locale messages.
 */
class ModuleExtension extends ExtensionAbstract
{
	const TYPE_MODULE = "icanboogie-module";
	const OPTION_MODULES_PATH = 'modules-path';

	/**
	 * @var array
	 */
	private $modules_directories;

	/**
	 * @inheritdoc
	 */
	public function alter_schema(callable $set_property)
	{
		$set_property(self::OPTION_MODULES_PATH, [

			'type' => "string",
			'description' => "(root-only) Path to application's custom modules.",
			'required' => false,

		]);
	}

	/**
	 * @inheritdoc
	 */
	public function synthesize(array &$autoconfig)
	{
		$modules_directories = $this->modules_directories = $this->collect_modules_directories();

		foreach ($modules_directories as $pathname)
		{
			if (is_dir("$pathname/config"))
			{
				$autoconfig[Autoconfig::CONFIG_PATH][] = [

					$this->findShortestPathCode("$pathname/config"),
					Autoconfig::CONFIG_WEIGHT_MODULE

				];
			}

			if (is_dir("$pathname/locale"))
			{
				$autoconfig[Autoconfig::LOCALE_PATH][] = $this
					->findShortestPathCode("$pathname/locale");
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	public function render()
	{
		return $this->render_array_entry(
			ModuleAutoconfig::MODULES,
			$this->modules_directories,
			function ($directory) {
				return $this->findShortestPathCode($directory);
			}
		);
	}

	/**
	 * @return array
	 */
	private function collect_modules_directories()
	{
		return array_merge(
			$this->collect_modules_directories_from_packages(),
			$this->collect_modules_directories_from_root_package()
		);
	}

	/**
	 * @return array
	 */
	private function collect_modules_directories_from_packages()
	{
		$directories = [];

		foreach ($this->generator->packages as $pathname => $package)
		{
			if ($package->getType() != self::TYPE_MODULE)
			{
				continue;
			}

			$directories[] = $pathname;
		}

		return $directories;
	}

	/**
	 * @return array
	 */
	private function collect_modules_directories_from_root_package()
	{
		$package = $this->generator->root_package;
		$extra = $package->getExtra();

		if (empty($extra['icanboogie'][self::OPTION_MODULES_PATH]))
		{
			return [];
		}

		$directories = [];
		$iterator = new \DirectoryIterator(getcwd() . DIRECTORY_SEPARATOR . $extra['icanboogie'][self::OPTION_MODULES_PATH]);

		foreach ($iterator as $file)
		{
			if ($file->isDot() || $file->isFile())
			{
				continue;
			}

			$directories[] = $file->getPathname();
		}

		return $directories;
	}
}
