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

use PHPUnit\Framework\TestCase;
use function ICanBoogie\app;
use Symfony\Component\DependencyInjection\Container;

/**
 * @group integration
 */
final class ModuleContainerExtensionTest extends TestCase
{
	/**
	 * @var Container
	 */
	private $container;

	protected function setUp(): void
	{
		$this->container = app()->container;
	}

	/**
	 * @dataProvider provide_module
	 */
	public function test_modules_should_be_defined(string $module, string $class): void
	{
		$service = "module.$module";
		$this->assertTrue($this->container->has($service));
		$this->assertInstanceOf($class, $this->container->get($service));
	}

	public function provide_module(): array
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
