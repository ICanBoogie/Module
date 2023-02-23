<?php

namespace ICanBoogie\Module\Console;

use ICanBoogie\Binding\Module\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function getcwd;
use function implode;
use function is_string;
use function str_starts_with;
use function strlen;
use function substr;

final class ListModulesCommand extends Command
{
    protected static $defaultDescription = "List modules";

    public function __construct(
        private readonly Config $config,
        private readonly string $style,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rows = [];

        foreach ($this->config->descriptors as $descriptor) {
            $rows[] = [
                $descriptor->id,
                $descriptor->parent,
                implode(' ', $descriptor->required),
                implode(' ', $descriptor->models),
                self::cwd_relative($descriptor->path ?? ''),
            ];
        }

        $table = new Table($output);
        $table->setHeaders([ 'Id', 'Parent', 'Required', 'Models', 'Path' ]);
        $table->setRows($rows);
        $table->setStyle($this->style);
        $table->render();

        return Command::SUCCESS;
    }

    private static function cwd_relative(string $path): string
    {
        if (!$path) {
            return $path;
        }

        $cwd = getcwd();

        assert(is_string($cwd));

        if (str_starts_with($path, $cwd)) {
            return substr($path, strlen($cwd) + 1);
        }

        return $path;
    }
}
