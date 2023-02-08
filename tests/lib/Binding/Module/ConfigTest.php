<?php

namespace Test\ICanBoogie\Binding\Module;

use ICanBoogie\Binding\Module\Config;
use ICanBoogie\Module\Descriptor;
use PHPUnit\Framework\TestCase;
use Test\ICanBoogie\SetStateHelper;

final class ConfigTest extends TestCase
{
    public function test_export(): void
    {
        $config = new Config(
            [
                'articles' => [
                    Descriptor::ID => 'articles'
                ]
            ]
        );

        $actual = SetStateHelper::export_import($config);

        $this->assertEquals($config, $actual);
    }
}
