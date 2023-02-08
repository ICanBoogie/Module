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
use ArrayIterator;
use BadMethodCallException;
use ICanBoogie\ErrorCollection;
use ICanBoogie\Module;
use ICanBoogie\Module\ModuleCollection\InstallableFilter;
use IteratorAggregate;
use Traversable;

use function substr;

/**
 * A module collection.
 *
 * @property-read array $descriptors Modules descriptors.
 */
class ModuleCollection implements ArrayAccess, IteratorAggregate
{
    /**
     * Instantiated modules.
     *
     * @var array<string, Module>
     *     Where _key_ is a module identifier.
     */
    private array $modules = [];

    /**
     * @param array<string, array<Descriptor::*, mixed>> $descriptors
     *     Where _key_ is a module identifier.
     */
    public function __construct(
        public array $descriptors,
    ) {
    }

    /**
     * The method is not supported.
     *
     * @inheritdoc
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException();
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException();
    }

    /**
     * Checks if a module exists.
     *
     * Note: `empty()` will call {@link offsetGet()} to check if the value is not empty. So, unless
     * you want to use the module you check, better check using `!isset()`, otherwise the module
     * you check is loaded too.
     *
     * @param string $offset Module identifier.
     *
     * @return bool Whether the module is available.
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->descriptors[$offset]);
    }

    /**
     * Returns a module object.
     *
     * If the {@link autorun} property is `true`, the {@link Module::run()} method of the module
     * is invoked upon its first loading.
     *
     * @param string $offset Module identifier.
     *
     * @return Module
     *
     * @throws ModuleConstructorMissing when the class that should be used to create its instance
     * is not defined.
     *
     * @throws ModuleNotDefined when the requested module is not defined.
     *
     */
    public function offsetGet(mixed $offset): Module
    {
        if (!$this->offsetExists($offset)) {
            throw new ModuleNotDefined($offset);
        }

        return $this->modules[$offset] ??= $this->instantiate_module($offset);
    }

    /**
     * Returns an iterator for instantiated modules.
     *
     * @return Traversable<string, Module>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->modules);
    }

    public function filter_descriptors(callable $filter): array
    {
        return array_filter($this->descriptors, $filter);
    }

    public function filter_descriptors_by_users(string $module_id): array
    {
        $users = [];
        $descriptors = $this->descriptors;

        foreach ($descriptors as $user_id => $descriptor) {
            if ($descriptor[Descriptor::PARENT] == $module_id
                || in_array($module_id, $descriptor[Descriptor::REQUIRES])) {
                $users[$user_id] = $descriptor;
            }
        }

        return $users;
    }

    /**
     * Returns the usage of a module by other modules.
     *
     * @param string $module_id The identifier of the module.
     *
     * @return int
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
     *
     * @return boolean `true` if the module inherits from the other.
     */
    public function is_inheriting(string $module_id, string $parent_id): bool
    {
        while ($module_id) {
            if ($module_id == $parent_id) {
                return true;
            }

            $descriptor = $this->descriptors[$module_id];

            $module_id = empty($descriptor[Descriptor::PARENT]) ? null : $descriptor[Descriptor::PARENT];
        }

        return false;
    }

    /**
     * Install all the enabled modules.
     *
     * @param ErrorCollection|null $errors
     *
     * @return ErrorCollection
     *
     * @throws ModuleCollectionInstallFailed if an error occurs.
     */
    public function install(ErrorCollection $errors = null): ErrorCollection
    {
        if (!$errors) {
            $errors = new ErrorCollection;
        }

        foreach (array_keys($this->filter_descriptors(new InstallableFilter($this))) as $module_id) {
            try {
                $this[$module_id]->install($errors);
            } catch (\Throwable $e) {
                $errors[$module_id] = $e;
            }
        }

        if ($errors->count()) {
            throw new ModuleCollectionInstallFailed($errors);
        }

        return $errors;
    }

    /**
     * Resolves a class name using module inheritance.
     *
     * To resolve a given class name, the method checks in each module namespace—starting from the
     * specified module—if the class exists. If it does, it returns its fully qualified name.
     *
     * @param string $unqualified_classname
     * @param string|Module $module_id
     * @param array $tried
     *
     * @return string|false The resolved file name, or `false` if it could not be resolved.
     *
     * @throws ModuleNotDefined if the specified module, or the module specified by
     * {@link Descriptor::PARENT} is not defined.
     */
    public function resolve_classname(string $unqualified_classname, string $module_id, array &$tried = [])
    {
        while ($module_id) {
            $descriptor = $this->descriptors[$module_id];
            $class = $descriptor[Descriptor::CLASSNAME];
            $pos = strrpos($class, '\\');
            $namespace = substr($class, 0, $pos);

            $fully_qualified_classname = $namespace . '\\' . $unqualified_classname;
            $tried[] = $fully_qualified_classname;

            if (class_exists($fully_qualified_classname, true)) {
                return $fully_qualified_classname;
            }

            $module_id = $descriptor[Descriptor::PARENT];
        }

        return false;
    }

    private function assert_constructor_exists(string $module_id, string $class): void
    {
        if (!class_exists($class, true)) {
            throw new ModuleConstructorMissing($module_id, $class);
        }
    }

    private function instantiate_module(string $module_id): Module
    {
        $descriptor = $this->descriptors[$module_id];
        $class = $descriptor[Descriptor::CLASSNAME];

        $this->assert_constructor_exists($module_id, $class);

        $parent = &$descriptor[Descriptor::PARENT];

        if ($parent) {
            $parent = $this[$parent];
        }

        return new $class($this, $descriptor);
    }
}
