<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\ICanBoogie\Module;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function ICanBoogie\app;

/**
 * @group integration
 */
final class ModuleContainerExtensionTest extends TestCase
{
	private ContainerInterface $container;

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
}
