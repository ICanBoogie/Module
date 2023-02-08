<?php

namespace ICanBoogie\Binding\Module;

use ICanBoogie\Config\Builder;
use ICanBoogie\Module\Descriptor;
use LogicException;

use function array_merge;
use function rtrim;

use const DIRECTORY_SEPARATOR;

/**
 * Build configuration from `module` fragments.
 *
 * The configuration is made of an array of descriptors, ordered by weight.
 * Their weight is computed from their parent and required modules.
 */
final class ConfigBuilder implements Builder
{
    public static function get_fragment_filename(): string
    {
        return 'module';
    }

    /**
     * @var array<string, array<Descriptor::*, mixed>>
     *     Where _key_ is a module identifier.
     */
    private array $descriptors = [];

    public function build(): object
    {
        $this->assert_parents();
        $this->assert_required();
        $this->resolve_ancestors();
        $this->order_by_weight();

        return new Config($this->descriptors);
    }

    /**
     * @param string $id
     *     The identifier of the module.
     * @param class-string $class
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

        $this->descriptors[$id] = [

            Descriptor::ID => $id,
            Descriptor::CLASSNAME => $class,
            Descriptor::PARENT => $parent,
            Descriptor::REQUIRES => $require,
            Descriptor::MODELS => $models,
            Descriptor::PATH => $path,

        ];

        return $this;
    }

    private function assert_parents(): void
    {
        foreach ($this->descriptors as $id => $descriptor) {
            $parent = $descriptor[Descriptor::PARENT];

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
            foreach ($descriptor[Descriptor::REQUIRES] as $required) {
                if ($required === $id) {
                    throw new LogicException("module '$id' cannot self-require");
                }

                if (empty($this->descriptors[$required])) {
                    throw new LogicException("module '$id' cannot require undefined module '$required'");
                }
            }
        }
    }

    private function resolve_ancestors(): void
    {
        /**
         * @param string[] $ancestors
         *
         * @return string[]
         */
        $find_ancestors = function (string $id, array &$ancestors = []) use (&$find_ancestors): array {
            $parent = $this->descriptors[$id][Descriptor::PARENT];

            if ($parent) {
                $ancestors[] = $parent;

                $find_ancestors($parent, $ancestors);
            }

            return $ancestors;
        };

        foreach ($this->descriptors as $id => &$descriptor) {
            $descriptor['ancestors'] = $find_ancestors($id);
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

            if ($descriptor[Descriptor::PARENT]) {
                $i += 1 + $compute_weight($descriptor[Descriptor::PARENT]);
            }

            foreach ($descriptor[Descriptor::REQUIRES] as $required) {
                $i += 1 + $compute_weight($required);
            }

            return $weights[$id] = $i;
        };

        foreach ($descriptors as $id => &$descriptor) {
            $descriptor[Descriptor::WEIGHT] = $compute_weight($id);
        }

        asort($weights);

        $this->descriptors = array_merge($weights, $descriptors);
    }
}
