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
 * Exception thrown when a requested module is not defined.
 *
 * @property-read string $module_id The identifier of the module that is not defined.
 */
class ModuleNotDefined extends \RuntimeException
{
	use \ICanBoogie\GetterTrait;

	/**
	 * Identifier of the module.
	 *
	 * @var string
	 */
	private $module_id;

	protected function get_module_id()
	{
		return $this->module_id;
	}

	public function __construct($module_id, $code=500, \Exception $previous=null)
	{
		$this->module_id = $module_id;

		parent::__construct(\ICanBoogie\format('Module is not defined: %module_id', [

			'module_id' => $module_id

		]), $code, $previous);
	}
}
