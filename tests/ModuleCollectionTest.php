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

class ModuleCollectionTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider provide_test_resolve_classname
	 *
	 * @param ModuleCollection $modules
	 * @param $classname
	 * @param $module_id
	 * @param $expected
	 */
	public function test_resolve_classname(ModuleCollection $modules, $classname, $module_id, $expected)
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

	public function provide_test_resolve_classname()
	{
		$modules = new ModuleCollection([ __DIR__ . DIRECTORY_SEPARATOR . 'modules' ]);
		$modules->index;

		return [

			[ $modules, 'AAAOperation', 'a', 'ICanBoogie\Module\ModulesTest\ModuleA\AAAOperation' ],
			[ $modules, 'AAAOperation', 'b', 'ICanBoogie\Module\ModulesTest\ModuleA\AAAOperation' ],
			[ $modules, 'AAAOperation', 'c', 'ICanBoogie\Module\ModulesTest\ModuleA\AAAOperation' ],

			[ $modules, 'BBBOperation', 'a', false ],
			[ $modules, 'BBBOperation', 'b', 'ICanBoogie\Module\ModulesTest\ModuleB\BBBOperation' ],
			[ $modules, 'BBBOperation', 'c', 'ICanBoogie\Module\ModulesTest\ModuleB\BBBOperation' ],

			[ $modules, 'CCCOperation', 'a', false ],
			[ $modules, 'CCCOperation', 'b', false ],
			[ $modules, 'CCCOperation', 'c', 'ICanBoogie\Module\ModulesTest\ModuleC\CCCOperation' ],

			[ $modules, 'DDDOperation', 'a', false ],
			[ $modules, 'DDDOperation', 'b', false ],
			[ $modules, 'DDDOperation', 'c', false ],

			[ $modules, 'ABCOperation', 'a', 'ICanBoogie\Module\ModulesTest\ModuleA\ABCOperation' ],
			[ $modules, 'ABCOperation', 'b', 'ICanBoogie\Module\ModulesTest\ModuleB\ABCOperation' ],
			[ $modules, 'ABCOperation', 'c', 'ICanBoogie\Module\ModulesTest\ModuleC\ABCOperation' ],

			[ $modules, 'AACOperation', 'a', 'ICanBoogie\Module\ModulesTest\ModuleA\AACOperation' ],
			[ $modules, 'AACOperation', 'b', 'ICanBoogie\Module\ModulesTest\ModuleA\AACOperation' ],
			[ $modules, 'AACOperation', 'c', 'ICanBoogie\Module\ModulesTest\ModuleC\AACOperation' ]

		];
	}

	public function test_get_module()
	{
		$modules = new ModuleCollection([ __DIR__ . DIRECTORY_SEPARATOR . 'modules' ]);
		$modules->index;

		$c = $modules['c'];
		$b = $modules['b'];

		$this->assertInstanceOf(Module::class, $c);
		$this->assertInstanceOf(Module::class, $b);
		$this->assertSame($b, $c->parent);
	}

	public function test_modules_weight()
	{
		$modules = new ModuleCollection([ __DIR__ . DIRECTORY_SEPARATOR . 'modules' ]);
		$modules->index;

		$this->assertSame([ 'b' ], $modules->descriptors['d'][Descriptor::REQUIRES]);
		$this->assertSame([ 'a', 'b', 'c', 'd' ], array_keys($modules->descriptors));
	}
}
