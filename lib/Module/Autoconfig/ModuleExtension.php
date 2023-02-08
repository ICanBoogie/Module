<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module\Autoconfig;

use DirectoryIterator;
use ICanBoogie\Autoconfig\Autoconfig;
use ICanBoogie\Autoconfig\AutoconfigGenerator;
use ICanBoogie\Autoconfig\ExtensionAbstract;

use function array_merge;
use function getcwd;
use function is_dir;

/**
 * Autoconfig extension to handle modules, their config and locale messages.
 */
final class ModuleExtension extends ExtensionAbstract
{
    public const TYPE_MODULE = "icanboogie-module";
    public const OPTION_MODULES_PATH = 'modules-path';

    private AutoconfigGenerator $generator;

    public function __construct(AutoconfigGenerator $generator)
    {
        $this->generator = $generator;

        parent::__construct($generator);
    }

    /**
     * @var string[]
     */
    private array $modules_directories;

    /**
     * @inheritdoc
     */
    public function alter_schema(callable $set_property): void
    {
        $set_property(self::OPTION_MODULES_PATH, [

            'type' => "string",
            'description' => "(root-only) Path to application's custom modules.",
            'required' => false,

        ]);
    }

    /**
     * @inheritdoc
     */
    public function synthesize(array &$autoconfig): void
    {
        $modules_directories = $this->modules_directories = $this->collect_modules_directories();

        foreach ($modules_directories as $pathname) {
            if (is_dir("$pathname/config")) {
                $autoconfig[Autoconfig::CONFIG_PATH][] = [

                    $this->find_shortest_path_code("$pathname/config"),
                    Autoconfig::CONFIG_WEIGHT_MODULE

                ];
            }

            if (is_dir("$pathname/locale")) {
                $autoconfig[Autoconfig::LOCALE_PATH][] = $this
                    ->find_shortest_path_code("$pathname/locale");
            }
        }
    }

    public function render(): string
    {
        return $this->render_array_entry(
            ModuleAutoconfig::MODULES,
            $this->modules_directories,
            function ($directory) {
                return $this->find_shortest_path_code($directory);
            }
        );
    }

    /**
     * @return string[]
     */
    private function collect_modules_directories(): array
    {
        return array_merge(
            $this->collect_modules_directories_from_packages(),
            $this->collect_modules_directories_from_root_package()
        );
    }

    /**
     * @return string[]
     */
    private function collect_modules_directories_from_packages(): array
    {
        $directories = [];

        foreach ($this->generator->packages as $pathname => $package) {
            if ($package->getType() != self::TYPE_MODULE) {
                continue;
            }

            $directories[] = $pathname;
        }

        return $directories;
    }

    private function collect_modules_directories_from_root_package(): array
    {
        $package = $this->generator->root_package;
        $extra = $package->getExtra();

        if (empty($extra['icanboogie'][self::OPTION_MODULES_PATH])) {
            return [];
        }

        $directories = [];
        $iterator = new DirectoryIterator(
            getcwd() . DIRECTORY_SEPARATOR . $extra['icanboogie'][self::OPTION_MODULES_PATH]
        );

        foreach ($iterator as $file) {
            if ($file->isDot() || $file->isFile()) {
                continue;
            }

            $directories[] = $file->getPathname();
        }

        return $directories;
    }
}
