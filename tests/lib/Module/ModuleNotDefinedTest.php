<?php

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
