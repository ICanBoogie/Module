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
 * Exception thrown when a class is missing to instantiate a module.
 *
 * @property-read string $module_id The identifier of the module.
 * @property-read string $class The name of the missing class.
 */
class ModuleConstructorMissing extends \RuntimeException
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

	/**
	 * Class name of the module.
	 *
	 * @var string
	 */
	private $class;

	protected function get_class()
	{
		return $this->class;
	}

	public function __construct($module_id, $class, $code=500, \Exception $previous=null)
	{
		$this->module_id = $module_id;
		$this->class = $class;

		parent::__construct(\ICanBoogie\format('Missing class %class to instantiate module %id.', [ 'class' => $class, 'id' => $module_id ]));
	}
}
