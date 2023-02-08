<?php

namespace Test\ICanBoogie\Binding\Module;

use ICanBoogie\Binding\Module\Config;
use ICanBoogie\Module\Descriptor;
use PHPUnit\Framework\TestCase;

use function dirname;
use function ICanBoogie\app;

final class ConfigBuilderTest extends TestCase
{
    public function testConfig(): void
    {
        $config = app()->configs->config_for_class(Config::class);

        $this->assertSame([

            'a' => [
                Descriptor::ID => 'a',
                Descriptor::CLASSNAME => \Test\ICanBoogie\Module\ModulesTest\ModuleA\Module::class,
                Descriptor::PARENT => null,
                Descriptor::REQUIRES => [],
                Descriptor::MODELS => [ 'a' ],
                Descriptor::PATH => dirname(__DIR__, 3) . '/modules/a/',
                Descriptor::ANCESTORS => [],
                Descriptor::WEIGHT => 0,
            ],
            'sample' => [
                Descriptor::ID => 'sample',
                Descriptor::CLASSNAME => \Test\ICanBoogie\Module\ModulesTest\Sample\Module::class,
                Descriptor::PARENT => null,
                Descriptor::REQUIRES => [],
                Descriptor::MODELS => [],
                Descriptor::PATH => null,
                Descriptor::ANCESTORS => [],
                Descriptor::WEIGHT => 0,
            ],
            'b' => [
                Descriptor::ID => 'b',
                Descriptor::CLASSNAME => \Test\ICanBoogie\Module\ModulesTest\ModuleB\Module::class,
                Descriptor::PARENT => 'a',
                Descriptor::REQUIRES => [],
                Descriptor::MODELS => [],
                Descriptor::PATH => null,
                Descriptor::ANCESTORS => [ 'a' ],
                Descriptor::WEIGHT => 1,
            ],
            'c' => [
                Descriptor::ID => 'c',
                Descriptor::CLASSNAME => \Test\ICanBoogie\Module\ModulesTest\ModuleC\Module::class,
                Descriptor::PARENT => 'b',
                Descriptor::REQUIRES => [],
                Descriptor::MODELS => [],
                Descriptor::PATH => null,
                Descriptor::ANCESTORS => [ 'b', 'a' ],
                Descriptor::WEIGHT => 2,
            ],
            'd' => [
                Descriptor::ID => 'd',
                Descriptor::CLASSNAME => \Test\ICanBoogie\Module\ModulesTest\ModuleD\Module::class,
                Descriptor::PARENT => null,
                Descriptor::REQUIRES => [ 'b' ],
                Descriptor::MODELS => [],
                Descriptor::PATH => null,
                Descriptor::ANCESTORS => [],
                Descriptor::WEIGHT => 2,
            ],

        ], $config->descriptors);
    }
}
