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

/**
 * {@link \ICanBoogie\Core} prototype bindings.
 *
 * @property ModuleCollection $modules
 * @property ModelCollection $models
 */
trait CoreBindings
{
	/**
	 * @return ModuleCollection
	 *
	 * @see \ICanBoogie\Module\Hooks::get_modules
	 */
	protected function lazy_get_modules()
	{
		return parent::lazy_get_modules();
	}

	/**
	 * @return ModelCollection
	 *
	 * @see \ICanBoogie\Module\Hooks::get_models
	 */
	protected function lazy_get_models()
	{
		return parent::lazy_get_models();
	}
}
