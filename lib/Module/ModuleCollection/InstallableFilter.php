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
class InstallableFilter
{
	/**
	 * @var ModuleCollection
	 */
	private $modules;

	/**
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
		$module = $this->modules[$descriptor[Descriptor::ID]];
		$errors = new ErrorCollection;

		try
		{
			$is_installed = $module->is_installed($errors);

			if ($is_installed && !count($errors))
			{
				return false;
			}
		}
		catch (\Exception $e)
		{
			# there was an error, the module might not be properly installed.
		}

		return true;
	}
}
