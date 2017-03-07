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

use function ICanBoogie\app;
use Symfony\Component\DependencyInjection\Container;

/**
 * @group integration
 */
class ModuleContainerExtensionTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var Container
	 */
	private $container;

	public function setUp()
	{
		$container = &$this->container;

		if (!$container)
		{
			$container = app()->container;
		}
	}

	/**
	 * @dataProvider provide_module
	 *
	 * @param string $module
	 * @param string $class
	 */
	public function test_modules_should_be_defined($module, $class)
	{
		$service = "module.$module";
		$this->assertTrue($this->container->has($service));
		$this->assertInstanceOf($class, $this->container->get($service));
	}

	/**
	 * @return array
	 */
	public function provide_module()
	{
		return [

			[ 'a', ModulesTest\ModuleA\Module::class ],
			[ 'b', ModulesTest\ModuleB\Module::class ],
			[ 'c', ModulesTest\ModuleC\Module::class ],
			[ 'sample', ModulesTest\Sample\Module::class ],

		];
	}

	public function test_models_should_be_defined()
	{
		$this->assertTrue($this->container->has("model.a"));
	}

	public function test_models_should_not_be_defined()
	{
		$this->assertFalse($this->container->has("model.b"));
		$this->assertFalse($this->container->has("model.c"));
		$this->assertFalse($this->container->has("model.sample"));
	}
}
