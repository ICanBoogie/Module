<?php

namespace ICanBoogie\Module\Autoconfig;

use DirectoryIterator;
use ICanBoogie\Autoconfig\Autoconfig;
use ICanBoogie\Autoconfig\AutoconfigGenerator;
use ICanBoogie\Autoconfig\ExtensionAbstract;

use function array_merge;
use function getcwd;
use function is_dir;
use function is_string;

/**
 * Autoconfig extension to handle modules, their config and locale messages.
 */
final class ModuleExtension extends ExtensionAbstract
{
    /**
     * Identifier for module packages.
     */
    public const string PACKAGE_TYPE_MODULE = "icanboogie-module";

    /**
     * composer.json extra property for modules path. (root-only)
     */
    public const string OPTION_MODULES_PATH = 'modules-path';

    public function __construct(
        private readonly AutoconfigGenerator $generator
    ) {
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
                /** @phpstan-ignore-next-line */
                $autoconfig[Autoconfig::ARG_CONFIG_PATHS][] = [

                    $this->find_shortest_path_code("$pathname/config"),
                    Autoconfig::CONFIG_WEIGHT_MODULE

                ];
            }

            if (is_dir("$pathname/locale")) {
                /** @phpstan-ignore-next-line */
                $autoconfig[Autoconfig::ARG_LOCALE_PATHS][] = $this
                    ->find_shortest_path_code("$pathname/locale");
            }
        }
    }

    public function render(): string
    {
        return $this->render_array_entry(
            ModuleAutoconfig::MODULES,
            $this->modules_directories, // @phpstan-ignore-line
            function ($directory) {
                return $this->find_shortest_path_code($directory); // @phpstan-ignore-line
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
            if ($package->getType() != self::PACKAGE_TYPE_MODULE) {
                continue;
            }

            $directories[] = $pathname;
        }

        return $directories;
    }

    /**
     * @return string[]
     */
    private function collect_modules_directories_from_root_package(): array
    {
        $package = $this->generator->root_package;
        /** @phpstan-ignore-next-line */
        $path = $package->getExtra()['icanboogie'][self::OPTION_MODULES_PATH] ?? null;

        if (!$path) {
            return [];
        }

        assert(is_string($path));

        $directories = [];
        $iterator = new DirectoryIterator(getcwd() . DIRECTORY_SEPARATOR . $path);

        foreach ($iterator as $file) {
            if ($file->isDot() || $file->isFile()) {
                continue;
            }

            $directories[] = $file->getPathname();
        }

        return $directories;
    }
}
