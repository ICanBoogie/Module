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

use Throwable;

use function ICanBoogie\format;

/**
 * Exception thrown when a requested module is not defined.
 *
 * @property-read string $module_id The identifier of the module that is not defined.
 */
final class ModuleNotDefined extends \RuntimeException
{
	/**
	 * @uses get_module_id
	 */
	use AccessorTrait;

	/**
	 * Identifier of the module.
	 *
	 * @var string
	 */
	private $module_id;

	private function get_module_id(): string
	{
		return $this->module_id;
	}

	public function __construct(string $module_id, Throwable $previous = null)
	{
		$this->module_id = $module_id;

		parent::__construct(format('Module is not defined: %module_id', [

			'module_id' => $module_id

		]), 0, $previous);
	}
}
