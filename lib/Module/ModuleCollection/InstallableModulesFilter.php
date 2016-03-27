<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module\ModuleCollection;

use ICanBoogie\ErrorCollection;
use ICanBoogie\Module\Descriptor;
use ICanBoogie\Module\ModuleCollection;

/**
 * Filters installable module descriptors.
 */
class InstallableModulesFilter
{
	/**
	 * @var ModuleCollection
	 */
	private $modules;

	/**
	 * InstallableModulesFilter constructor.
	 *
	 * @param ModuleCollection $modules
	 */
	public function __construct(ModuleCollection $modules)
	{
		$this->modules = $modules;
	}

	/**
	 * @param array $descriptor An array of {@link Descriptor::*} keys.
	 *
	 * @return bool `true` if the module may be installed, `false` otherwise.
	 */
	public function __invoke(array $descriptor)
	{
		if ($descriptor[Descriptor::DISABLED])
		{
			return false;
		}

		$module = $this->modules[$descriptor[Descriptor::ID]];
		$errors = new ErrorCollection;
		$is_installed = $module->is_installed($errors);

		return $is_installed === false || $errors->count();
	}
}
