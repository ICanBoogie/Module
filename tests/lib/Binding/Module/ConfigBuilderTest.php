<?php

namespace Test\ICanBoogie\Binding\Module;

use ICanBoogie\Binding\Module\Config;
use ICanBoogie\Module\Descriptor;
use PHPUnit\Framework\TestCase;
use Test\ICanBoogie\Module\ModulesTest\ModuleD\Module;

use function array_keys;
use function dirname;
use function ICanBoogie\app;

final class ConfigBuilderTest extends TestCase
{
    public function testConfig(): void
    {
        $config = app()->configs->config_for_class(Config::class);

        $this->assertSame(
            [ 'a', 'sample', 'b', 'c', 'd' ],
            array_keys($config->descriptors)
        );

        $this->assertEquals([

            'a' => new Descriptor(
                id: 'a',
                class: \Test\ICanBoogie\Module\ModulesTest\ModuleA\Module::class,
                models: [ 'a' ],
                path: dirname(__DIR__, 3) . '/modules/a/',
            ),
            'sample' => new Descriptor(
                id: 'sample',
                class: \Test\ICanBoogie\Module\ModulesTest\Sample\Module::class,
            ),
            'b' => new Descriptor(
                id: 'b',
                class: \Test\ICanBoogie\Module\ModulesTest\ModuleB\Module::class,
                parent: 'a',
            ),
            'c' => new Descriptor(
                id: 'c',
                class: \Test\ICanBoogie\Module\ModulesTest\ModuleC\Module::class,
                parent: 'b',
            ),
            'd' => new Descriptor(
                id: 'd',
                class: Module::class,
                required: [ 'b' ],
            ),

        ], $config->descriptors);
    }
}
