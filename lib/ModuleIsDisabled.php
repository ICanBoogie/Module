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

use ICanBoogie\Accessor\AccessorTrait;

/**
 * Exception thrown when a disabled module is requested.
 *
 * @property-read string $module_id The identifier of the disabled module.
 */
class ModuleIsDisabled extends \RuntimeException
{
	use AccessorTrait;

	private $module_id;

	protected function get_module_id()
	{
		return $this->module_id;
	}

	public function __construct($module_id, $code=500, \Exception $previous=null)
	{
		$this->module_id = $module_id;

		parent::__construct(\ICanBoogie\format('Module is disabled: %module_id', [

			'module_id' => $module_id

		]), $code, $previous);
	}
}
