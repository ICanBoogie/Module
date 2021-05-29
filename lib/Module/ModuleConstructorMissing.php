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

use RuntimeException;

use Throwable;

use function ICanBoogie\format;

/**
 * Exception thrown when a class is missing to instantiate a module.
 *
 * @property-read string $module_id The identifier of the module.
 * @property-read string $class The name of the missing class.
 */
final class ModuleConstructorMissing extends RuntimeException
{
	/**
	 * @uses get_module_id
	 * @uses get_class
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

	/**
	 * Class name of the module.
	 *
	 * @var string
	 */
	private $class;

	private function get_class(): string
	{
		return $this->class;
	}

	public function __construct(string $module_id, string $class, Throwable $previous = null)
	{
		$this->module_id = $module_id;
		$this->class = $class;

		parent::__construct(format('Missing class %class to instantiate module %id.', [

			'class' => $class,
			'id' => $module_id

		]), 0, $previous);
	}
}
