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

final class ModuleConstructorMissingTest extends TestCase
{
	public function test_instance()
	{
		$module_id = uniqid();
		$class = uniqid();
		$instance = new ModuleConstructorMissing($module_id, $class);
		$this->assertSame($module_id, $instance->module_id);
		$this->assertSame($class, $instance->class);
	}
}
