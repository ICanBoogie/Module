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

use ICanBoogie\Module\ModuleNotDefined;
use PHPUnit\Framework\TestCase;

final class ModuleNotDefinedTest extends TestCase
{
    public function test_instance(): void
    {
        $module_id = uniqid();
        $instance = new ModuleNotDefined($module_id);
        $this->assertSame($module_id, $instance->module_id);
    }
}
