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

class ModulesTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider provide_test_resolve_classname
	 */
	public function test_resolve_classname(Modules $modules, $classname, $module_id, $expected)
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
		$modules = new Modules([ __DIR__ . DIRECTORY_SEPARATOR . 'modules' ]);
		$modules->index;

		return [

			[ $modules, 'AAAOperation', 'a', 'ICanBoogie\Module\ModulesTest\ModuleA\AAAOperation' ],
			[ $modules, 'AAAOperation', 'b', 'ICanBoogie\Module\ModulesTest\ModuleA\AAAOperation' ],
			[ $modules, 'AAAOperation', 'c', 'ICanBoogie\Module\ModulesTest\ModuleA\AAAOperation' ],

			[ $modules, 'BBBOperation', 'a', null ],
			[ $modules, 'BBBOperation', 'b', 'ICanBoogie\Module\ModulesTest\ModuleB\BBBOperation' ],
			[ $modules, 'BBBOperation', 'c', 'ICanBoogie\Module\ModulesTest\ModuleB\BBBOperation' ],

			[ $modules, 'CCCOperation', 'a', null ],
			[ $modules, 'CCCOperation', 'b', null ],
			[ $modules, 'CCCOperation', 'c', 'ICanBoogie\Module\ModulesTest\ModuleC\CCCOperation' ],

			[ $modules, 'DDDOperation', 'a', null ],
			[ $modules, 'DDDOperation', 'b', null ],
			[ $modules, 'DDDOperation', 'c', null ],

			[ $modules, 'ABCOperation', 'a', 'ICanBoogie\Module\ModulesTest\ModuleA\ABCOperation' ],
			[ $modules, 'ABCOperation', 'b', 'ICanBoogie\Module\ModulesTest\ModuleB\ABCOperation' ],
			[ $modules, 'ABCOperation', 'c', 'ICanBoogie\Module\ModulesTest\ModuleC\ABCOperation' ],

			[ $modules, 'AACOperation', 'a', 'ICanBoogie\Module\ModulesTest\ModuleA\AACOperation' ],
			[ $modules, 'AACOperation', 'b', 'ICanBoogie\Module\ModulesTest\ModuleA\AACOperation' ],
			[ $modules, 'AACOperation', 'c', 'ICanBoogie\Module\ModulesTest\ModuleC\AACOperation' ]

		];
	}
}