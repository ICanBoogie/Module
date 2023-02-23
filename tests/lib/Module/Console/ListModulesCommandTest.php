<?php

namespace Test\ICanBoogie\Module\Console;

use ICanBoogie\Console\Test\CommandTestCase;
use ICanBoogie\Module\Console\ListModulesCommand;

final class ListModulesCommandTest extends CommandTestCase
{
    public static function provideExecute(): array
    {
        return [

            [
                'modules',
                ListModulesCommand::class,
                [],
                [
                    'a',
                    '',
                    '',
                    'a',
                    'modules/a/'
                ]
            ],

        ];
    }

    public function testAlias(): void
    {
        $loader = $this->getCommandLoader();
        $command1 = $loader->get('modules');
        $command2 = $loader->get('modules:list');

        $this->assertSame($command1, $command2);
    }
}
