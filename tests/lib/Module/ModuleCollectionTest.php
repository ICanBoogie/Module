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

use ICanBoogie\Module;
use ICanBoogie\Module\Descriptor;
use ICanBoogie\Module\ModuleCollection;
use PHPUnit\Framework\TestCase;
use Test\ICanBoogie\Module\ModulesTest\ModuleA;
use Test\ICanBoogie\Module\ModulesTest\ModuleB;
use Test\ICanBoogie\Module\ModulesTest\ModuleC;

final class ModuleCollectionTest extends TestCase
{
	/**
	 * @dataProvider provide_test_resolve_classname
	 *
	 * @param string|bool $expected
	 */
	public function test_resolve_classname(ModuleCollection $modules, string $classname, string $module_id, $expected)
	{
		if ($expected === null) {
			$this->assertNull($modules->resolve_classname($classname, $module_id));
		} else {
			$this->assertEquals($expected, $modules->resolve_classname($classname, $module_id));
		}
	}

	public function provide_test_resolve_classname(): array
	{
		$modules = $this->create_module_collection();
		$modules->descriptors;

		return [

			[ $modules, 'Operation\AAAOperation', 'a', ModuleA\Operation\AAAOperation::class ],
			[ $modules, 'Operation\AAAOperation', 'b', ModuleA\Operation\AAAOperation::class ],
			[ $modules, 'Operation\AAAOperation', 'c', ModuleA\Operation\AAAOperation::class ],

			[ $modules, 'Operation\BBBOperation', 'a', false ],
			[ $modules, 'Operation\BBBOperation', 'b', ModuleB\Operation\BBBOperation::class ],
			[ $modules, 'Operation\BBBOperation', 'c', ModuleB\Operation\BBBOperation::class ],

			[ $modules, 'Operation\CCCOperation', 'a', false ],
			[ $modules, 'Operation\CCCOperation', 'b', false ],
			[ $modules, 'Operation\CCCOperation', 'c', ModuleC\Operation\CCCOperation::class ],

			[ $modules, 'Operation\DDDOperation', 'a', false ],
			[ $modules, 'Operation\DDDOperation', 'b', false ],
			[ $modules, 'Operation\DDDOperation', 'c', false ],

			[ $modules, 'Operation\ABCOperation', 'a', ModuleA\Operation\ABCOperation::class ],
			[ $modules, 'Operation\ABCOperation', 'b', ModuleB\Operation\ABCOperation::class ],
			[ $modules, 'Operation\ABCOperation', 'c', ModuleC\Operation\ABCOperation::class ],

			[ $modules, 'Operation\AACOperation', 'a', ModuleA\Operation\AACOperation::class ],
			[ $modules, 'Operation\AACOperation', 'b', ModuleA\Operation\AACOperation::class ],
			[ $modules, 'Operation\AACOperation', 'c', ModuleC\Operation\AACOperation::class ]

		];
	}

	public function test_get_module(): void
	{
		$modules = $this->create_module_collection();
		$modules->descriptors;

		$c = $modules['c'];
		$b = $modules['b'];

		$this->assertInstanceOf(Module::class, $c);
		$this->assertInstanceOf(Module::class, $b);
		$this->assertSame($b, $c->parent);
	}

	public function test_modules_weight(): void
	{
		$modules = $this->create_module_collection();

		$this->assertSame([ 'b' ], $modules->descriptors['d'][Descriptor::REQUIRES]);
		$this->assertSame([ 'a', 'b', 'c', 'd', 'sample' ], array_keys($modules->descriptors));
	}

	private function create_module_collection(): ModuleCollection
	{
		return new ModuleCollection([

			getcwd() . '/modules/a',
			getcwd() . '/modules/b',
			getcwd() . '/modules/c',
			getcwd() . '/modules/d',
			getcwd() . '/modules/sample',

		]);
	}
}
