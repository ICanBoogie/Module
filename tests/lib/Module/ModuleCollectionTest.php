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

use ICanBoogie\Module;
use PHPUnit\Framework\TestCase;

final class ModuleCollectionTest extends TestCase
{
	/**
	 * @dataProvider provide_test_resolve_classname

	 * @param string|bool $expected
	 */
	public function test_resolve_classname(ModuleCollection $modules, string $classname, string $module_id, $expected)
	{
		if ($expected === null)
		{
			$this->assertNull($modules->resolve_classname($classname, $module_id));
		}
		else
		{
			$this->assertEquals($expected, $modules->resolve_classname($classname, $module_id));
		}
	}

	public function provide_test_resolve_classname(): array
	{
		$modules = $this->create_module_collection();
		$modules->descriptors;

		return [

			[ $modules, 'Operation\AAAOperation', 'a', 'ICanBoogie\Module\ModulesTest\ModuleA\Operation\AAAOperation' ],
			[ $modules, 'Operation\AAAOperation', 'b', 'ICanBoogie\Module\ModulesTest\ModuleA\Operation\AAAOperation' ],
			[ $modules, 'Operation\AAAOperation', 'c', 'ICanBoogie\Module\ModulesTest\ModuleA\Operation\AAAOperation' ],

			[ $modules, 'Operation\BBBOperation', 'a', false ],
			[ $modules, 'Operation\BBBOperation', 'b', 'ICanBoogie\Module\ModulesTest\ModuleB\Operation\BBBOperation' ],
			[ $modules, 'Operation\BBBOperation', 'c', 'ICanBoogie\Module\ModulesTest\ModuleB\Operation\BBBOperation' ],

			[ $modules, 'Operation\CCCOperation', 'a', false ],
			[ $modules, 'Operation\CCCOperation', 'b', false ],
			[ $modules, 'Operation\CCCOperation', 'c', 'ICanBoogie\Module\ModulesTest\ModuleC\Operation\CCCOperation' ],

			[ $modules, 'Operation\DDDOperation', 'a', false ],
			[ $modules, 'Operation\DDDOperation', 'b', false ],
			[ $modules, 'Operation\DDDOperation', 'c', false ],

			[ $modules, 'Operation\ABCOperation', 'a', 'ICanBoogie\Module\ModulesTest\ModuleA\Operation\ABCOperation' ],
			[ $modules, 'Operation\ABCOperation', 'b', 'ICanBoogie\Module\ModulesTest\ModuleB\Operation\ABCOperation' ],
			[ $modules, 'Operation\ABCOperation', 'c', 'ICanBoogie\Module\ModulesTest\ModuleC\Operation\ABCOperation' ],

			[ $modules, 'Operation\AACOperation', 'a', 'ICanBoogie\Module\ModulesTest\ModuleA\Operation\AACOperation' ],
			[ $modules, 'Operation\AACOperation', 'b', 'ICanBoogie\Module\ModulesTest\ModuleA\Operation\AACOperation' ],
			[ $modules, 'Operation\AACOperation', 'c', 'ICanBoogie\Module\ModulesTest\ModuleC\Operation\AACOperation' ]

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
