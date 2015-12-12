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
 * A trait implemented by module routes.
 *
 * @property-read string $module
 */
trait ModuleRoute
{
	/**
	 * @var string Module identifier.
	 */
	protected $module;

	/**
	 * @return string
	 */
	protected function get_module()
	{
		return $this->module;
	}
}
