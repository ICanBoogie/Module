<?php

namespace ICanBoogie\Module;

use ICanBoogie\Module;
use IteratorAggregate;

/**
 * @extends IteratorAggregate<string, (callable(): Module)>
 */
interface ModuleProvider extends IteratorAggregate
{
    /**
     * @template T of Module
     *
     * @param string $id
     *     A module identifier.
     * @param class-string<T> $class
     *     Optional type checking.
     *
     * @return T
     */
    public function module_for_id(string $id, string $class = Module::class): Module;

    /**
     * @param string $id
     *     A module identifier.
     */
    public function has_module(string $id): bool;
}
