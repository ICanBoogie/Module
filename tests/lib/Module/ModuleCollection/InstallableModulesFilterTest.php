<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module\ModuleCollection;

use ICanBoogie\Module;
use ICanBoogie\Module\Descriptor;
use ICanBoogie\Module\ModuleCollection;

/**
 * @small
 * @group install
 */
class InstallableModulesFilterTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider provide_test_filter
	 *
	 * @param bool $is_installed
	 * @param bool $has_errors
	 * @param $expected
	 */
	public function test_filter($is_installed, $has_errors, $expected)
	{
		$module_id = uniqid();

		$descriptor = [

			Descriptor::ID => $module_id,

		];

		$module = $this->mockModule($is_installed, $has_errors);
		$modules = $this->mockModules($module_id, $module);

		$filter = new InstallableFilter($modules);
		$this->assertSame($expected, $filter($descriptor));
	}

	/**
	 * @return array
	 */
	public function provide_test_filter()
	{
		return [

			[ false, false, true ],
			[ false, true, true ],
			[ true, true, true ],
			[ true, false, false ],

		];
	}

	/**
	 * @param bool $is_installed
	 * @param bool $has_errors
	 *
	 * @return Module
	 */
	private function mockModule($is_installed, $has_errors)
	{
		$module = $this
			->getMockBuilder(Module::class)
			->disableOriginalConstructor()
			->setMethods([ 'is_installed' ])
			->getMock();
		$module
			->expects($this->any())
			->method('is_installed')
			->willReturnCallback(function($errors) use ($is_installed, $has_errors) {

				if ($has_errors)
				{
					$errors[] = uniqid();
				}

				return $is_installed;

			});

		/* @var Module $module */

		return $module;
	}

	/**
	 * @param string $module_id
	 * @param Module $module
	 *
	 * @return ModuleCollection
	 */
	private function mockModules($module_id, $module)
	{
		$modules = $this
			->getMockBuilder(ModuleCollection::class)
			->disableOriginalConstructor()
			->setMethods([ 'offsetGet' ])
			->getMock();
		$modules
			->expects($this->any())
			->method('offsetGet')
			->with($module_id)
			->willReturn($module);

		/* @var ModuleCollection $modules */

		return $modules;
	}
}
