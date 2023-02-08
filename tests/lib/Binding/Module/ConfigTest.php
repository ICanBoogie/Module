<?php

namespace Test\ICanBoogie\Binding\Module;

use ICanBoogie\Binding\Module\Config;
use ICanBoogie\Module\Descriptor;
use PHPUnit\Framework\TestCase;
use Test\ICanBoogie\Module\ModulesTest\Sample\Module;
use Test\ICanBoogie\SetStateHelper;

final class ConfigTest extends TestCase
{
    public function test_export(): void
    {
        $config = new Config(
            [
                'articles' => new Descriptor('articles', Module::class),
            ]
        );

        $actual = SetStateHelper::export_import($config);

        $this->assertEquals($config, $actual);
    }
}
