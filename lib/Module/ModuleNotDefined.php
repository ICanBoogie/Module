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
use ICanBoogie\HTTP\Status;

use function ICanBoogie\format;

/**
 * Exception thrown when a requested module is not defined.
 *
 * @property-read string $module_id The identifier of the module that is not defined.
 */
class ModuleNotDefined extends \RuntimeException
{
	use AccessorTrait;

	/**
	 * Identifier of the module.
	 *
	 * @var string
	 */
	private $module_id;

	/**
	 * @return string
	 */
	protected function get_module_id()
	{
		return $this->module_id;
	}

	/**
	 * @param string $module_id
	 * @param int $code
	 * @param \Exception|null $previous
	 */
	public function __construct($module_id, $code = Status::INTERNAL_SERVER_ERROR, \Exception $previous = null)
	{
		$this->module_id = $module_id;

		parent::__construct(format('Module is not defined: %module_id', [

			'module_id' => $module_id

		]), $code, $previous);
	}
}
