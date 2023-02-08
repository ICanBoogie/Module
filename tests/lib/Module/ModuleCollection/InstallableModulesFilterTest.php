<?php

namespace Test\ICanBoogie\Module\ModuleCollection;

use ICanBoogie\ErrorCollection;
use ICanBoogie\Module;
use ICanBoogie\Module\Descriptor;
use ICanBoogie\Module\ModuleCollection;
use ICanBoogie\Module\ModuleProvider;
use PHPUnit\Framework\TestCase;

/**
 * @small
 * @group install
 */
final class InstallableModulesFilterTest extends TestCase
{
    /**
     * @dataProvider provide_test_filter
     */
    public function test_filter(bool $is_installed, bool $has_errors, bool $expected): void
    {
        $module_id = uniqid();
        $module = $this->mockModule($is_installed, $has_errors);
        $descriptor = new Descriptor($module_id, $module::class);

        $modules = $this->mockModuleProvider($module_id, $module);

        $filter = new ModuleCollection\InstallableFilter($modules);
        $this->assertSame($expected, $filter($descriptor));
    }

    /**
     * @return array<array{ bool, bool, bool }>
     */
    public function provide_test_filter(): array
    {
        return [

            [ false, false, true ],
            [ false, true, true ],
            [ true, true, true ],
            [ true, false, false ],

        ];
    }

    private function mockModule(bool $is_installed, bool $has_errors): Module
    {
        $module = $this
            ->getMockBuilder(Module::class)
            ->disableOriginalConstructor()
            ->onlyMethods([ 'is_installed' ])
            ->getMock();
        $module
            ->method('is_installed')
            ->willReturnCallback(function (ErrorCollection $errors) use ($is_installed, $has_errors): bool {
                if ($has_errors) {
                    $errors[] = uniqid();
                }

                return $is_installed;
            });

        return $module;
    }

    private function mockModuleProvider(string $module_id, Module $module): ModuleProvider
    {
        $provider = $this->createMock(ModuleProvider::class);
        $provider
            ->method('module_for_id')
            ->with($module_id)
            ->willReturn($module);

        return $provider;
    }
}
