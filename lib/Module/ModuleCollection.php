<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module;

use ArrayAccess;
use BadMethodCallException;
use ICanBoogie\Binding\Module\Config;
use ICanBoogie\Module;
use IteratorAggregate;
use Traversable;

use function substr;

/**
 * @implements ArrayAccess<string, Module>
 * @implements IteratorAggregate<string, (callable(): Module)>
 */
class ModuleCollection implements ArrayAccess, IteratorAggregate
{
    /**
     * @var array<string, Descriptor>
     *     Where _key_ is a module identifier.
     */
    public readonly array $descriptors;

    public function __construct(
        private readonly Config $config,
        private readonly ModuleProvider $provider
    ) {
        $this->descriptors = $this->config->descriptors;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->descriptors[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->provider->module_for_id($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException();
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException();
    }

    public function getIterator(): Traversable
    {
        return $this->provider->getIterator();
    }

    /**
     * @param (callable(Descriptor): bool) $filter
     *
     * @return array<string, Descriptor>
     */
    public function filter_descriptors(callable $filter): array
    {
        return array_filter($this->descriptors, $filter);
    }

    /**
     * @return array<string, Descriptor>
     */
    public function filter_descriptors_by_users(string $module_id): array
    {
        $users = [];
        $descriptors = $this->descriptors;

        foreach ($descriptors as $user_id => $descriptor) {
            if ($descriptor->parent == $module_id || in_array($module_id, $descriptor->required)) {
                $users[$user_id] = $descriptor;
            }
        }

        return $users;
    }

    /**
     * Returns the usage of a module by other modules.
     */
    public function usage(string $module_id): int
    {
        return count($this->filter_descriptors_by_users($module_id));
    }

    /**
     * Checks if a module inherits from another.
     *
     * @param string $module_id Module identifier.
     * @param string $parent_id Identifier of the parent module.
     */
    public function is_inheriting(string $module_id, string $parent_id): bool
    {
        while ($module_id) {
            if ($module_id === $parent_id) {
                return true;
            }

            $descriptor = $this->descriptors[$module_id];
            $module_id = $descriptor->parent;
        }

        return false;
    }

    /**
     * Resolves a class name using module inheritance.
     *
     * To resolve a given class name, the method checks in each module namespace—starting from the
     * specified module—if the class exists. If it does, it returns its fully qualified name.
     *
     * @param string[] $tried
     */
    public function resolve_classname(
        string $unqualified_classname,
        string $module_id,
        array &$tried = []
    ): string|false {
        while ($module_id) {
            $descriptor = $this->descriptors[$module_id];
            $class = $descriptor->class;
            $pos = strrpos($class, '\\');
            assert($pos !== false);
            $namespace = substr($class, 0, $pos);

            $fully_qualified_classname = $namespace . '\\' . $unqualified_classname;
            $tried[] = $fully_qualified_classname;

            if (class_exists($fully_qualified_classname)) {
                return $fully_qualified_classname;
            }

            $module_id = $descriptor->parent;
        }

        return false;
    }
}
