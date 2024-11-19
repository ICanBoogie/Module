<?php

namespace Test\ICanBoogie;

use ICanBoogie\Binding\Module\Config;
use ICanBoogie\Module\ModuleCollection;
use ICanBoogie\Module\ModuleInstaller;
use ICanBoogie\Module\ModuleProvider;
use ICanBoogie\Render\TemplateResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function ICanBoogie\app;

final class ContainerTest extends TestCase
{
    /**
     * @param class-string $class
     */
    #[DataProvider('provide_service')]
    public function test_service(string $id, string $class): void
    {
        $this->assertInstanceOf($class, app()->service_for_id($id, $class));
    }

    /**
     * @return array<array{ string, class-string }>
     */
    public static function provide_service(): array
    {
        return [

            [ 'test.config', Config::class ],
            [ 'test.module_installer', ModuleInstaller::class ],
            [ 'test.module_provider', ModuleProvider::class ],
            [ 'test.modules', ModuleCollection::class ],
            [ 'test.template_resolver', TemplateResolver::class ],

        ];
    }

    public function test_module_provider(): void
    {
        $provider = app()->service_for_id('test.module_provider', ModuleProvider::class);
        $module = $provider->module_for_id('sample');

        $this->assertInstanceOf(Module\ModulesTest\Sample\Module::class, $module);
    }
}
