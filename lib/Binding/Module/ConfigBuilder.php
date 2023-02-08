<?php

namespace ICanBoogie\Binding\Module;

use ICanBoogie\Config\Builder;
use ICanBoogie\Module;
use ICanBoogie\Module\Descriptor;
use LogicException;

use function rtrim;

use const DIRECTORY_SEPARATOR;

/**
 * Build configuration from `module` fragments.
 *
 * The configuration is made of an array of descriptors, ordered by weight.
 * Their weight is computed from their parent and required modules.
 *
 * @implements Builder<Config>
 */
final class ConfigBuilder implements Builder
{
    public static function get_fragment_filename(): string
    {
        return 'module';
    }

    /**
     * @var array<string, Descriptor>
     *     Where _key_ is a module identifier.
     */
    private array $descriptors = [];

    public function build(): object
    {
        $this->assert_parents();
        $this->assert_required();
        $this->order_by_weight();

        return new Config($this->descriptors);
    }

    /**
     * @param string $id
     *     The identifier of the module.
     * @param class-string<Module> $class
     *     The class of the module.
     * @param ?string $parent
     *     The parent module.
     * @param string[] $require
     *     The modules required by this module.
     * @param string[] $models
     *     The models associated to this module.
     * @param ?string $path
     *     Path to the root of the module. Might be useful.
     *
     * @return $this
     */
    public function add_module(
        string $id,
        string $class,
        string $parent = null,
        array $require = [],
        array $models = [],
        string $path = null
    ): self {
        if (isset($this->descriptors[$id])) {
            throw new LogicException("module '$id' already defined");
        }

        // ensures the path ends with a directory separator
        if ($path) {
            $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }

        $this->descriptors[$id] = new Descriptor(
            id: $id,
            class: $class,
            parent: $parent,
            required: $require,
            models: $models,
            path: $path
        );

        return $this;
    }

    private function assert_parents(): void
    {
        foreach ($this->descriptors as $id => $descriptor) {
            $parent = $descriptor->parent;

            if (!$parent) {
                continue;
            }

            if ($parent === $id) {
                throw new LogicException("module '$id' cannot be its own parent");
            }

            if (empty($this->descriptors[$parent])) {
                throw new LogicException("module '$id' have undefined parent '$parent'");
            }
        }
    }

    private function assert_required(): void
    {
        foreach ($this->descriptors as $id => $descriptor) {
            foreach ($descriptor->required as $required) {
                if ($required === $id) {
                    throw new LogicException("module '$id' cannot self-require");
                }

                if (empty($this->descriptors[$required])) {
                    throw new LogicException("module '$id' cannot require undefined module '$required'");
                }
            }
        }
    }

    private function order_by_weight(): void
    {
        $descriptors = $this->descriptors;

        /**
         * @var array<string, int> $weights
         *     Where _key_ is a module identifier and _value_ its weight.
         */
        $weights = [];

        $compute_weight = static function (string $id) use (&$compute_weight, &$weights, $descriptors) {
            if (isset($weights[$id])) {
                return $weights[$id];
            }

            $i = 0;
            $descriptor = $descriptors[$id];

            if ($descriptor->parent) {
                $i += 1 + $compute_weight($descriptor->parent);
            }

            foreach ($descriptor->required as $required) {
                $i += 1 + $compute_weight($required);
            }

            return $weights[$id] = $i;
        };

        foreach (array_keys($descriptors) as $id) {
            $compute_weight($id);
        }

        uksort(
            $this->descriptors,
            fn(string $id1, string $id2): int => $weights[$id1] <=> $weights[$id2]
        );
    }
}
