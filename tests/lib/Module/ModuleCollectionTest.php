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

use ICanBoogie\Module\ModuleCollection;
use PHPUnit\Framework\TestCase;
use Test\ICanBoogie\Module\ModulesTest\ModuleA;
use Test\ICanBoogie\Module\ModulesTest\ModuleB;
use Test\ICanBoogie\Module\ModulesTest\ModuleC;

use function ICanBoogie\app;

final class ModuleCollectionTest extends TestCase
{
    private static ModuleCollection $modules;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$modules = app()->service_for_id('test.modules', ModuleCollection::class);
    }

    /**
     * @dataProvider provide_test_resolve_classname
     *
     * @param class-string|false $expected
     */
    public function test_resolve_classname(string $classname, string $module_id, string|false $expected)
    {
        $actual = self::$modules->resolve_classname($classname, $module_id);

        $this->assertEquals($expected, $actual);
    }

    public function provide_test_resolve_classname(): array
    {
        return [

            [ 'Operation\AAAOperation', 'a', ModuleA\Operation\AAAOperation::class ],
            [ 'Operation\AAAOperation', 'b', ModuleA\Operation\AAAOperation::class ],
            [ 'Operation\AAAOperation', 'c', ModuleA\Operation\AAAOperation::class ],

            [ 'Operation\BBBOperation', 'a', false ],
            [ 'Operation\BBBOperation', 'b', ModuleB\Operation\BBBOperation::class ],
            [ 'Operation\BBBOperation', 'c', ModuleB\Operation\BBBOperation::class ],

            [ 'Operation\CCCOperation', 'a', false ],
            [ 'Operation\CCCOperation', 'b', false ],
            [ 'Operation\CCCOperation', 'c', ModuleC\Operation\CCCOperation::class ],

            [ 'Operation\DDDOperation', 'a', false ],
            [ 'Operation\DDDOperation', 'b', false ],
            [ 'Operation\DDDOperation', 'c', false ],

            [ 'Operation\ABCOperation', 'a', ModuleA\Operation\ABCOperation::class ],
            [ 'Operation\ABCOperation', 'b', ModuleB\Operation\ABCOperation::class ],
            [ 'Operation\ABCOperation', 'c', ModuleC\Operation\ABCOperation::class ],

            [ 'Operation\AACOperation', 'a', ModuleA\Operation\AACOperation::class ],
            [ 'Operation\AACOperation', 'b', ModuleA\Operation\AACOperation::class ],
            [ 'Operation\AACOperation', 'c', ModuleC\Operation\AACOperation::class ]

        ];
    }
}
