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

class ModuleNotDefinedTest extends \PHPUnit_Framework_TestCase
{
	public function test_instance()
	{
		$module_id = uniqid();
		$instance = new ModuleNotDefined($module_id);
		$this->assertSame($module_id, $instance->module_id);
	}
}
