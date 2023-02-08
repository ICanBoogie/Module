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

use ICanBoogie\Module;

/**
 * Module descriptor options.
 *
 * @package ICanBoogie\Module
 */
final class Descriptor
{
    /**
     * @param array{
     *     id: string,
     *     class: class-string<Module>,
     *     parent: ?string,
     *     required: string[],
     *     models: string[],
     *     path: ?string
     * } $an_array
     *
     * @return self
     */
    public static function __set_state(array $an_array): object
    {
        return new self(...$an_array);
    }

    /**
     * @param string $id
     *     The identifier of the module.
     * @param class-string<Module> $class
     *     The class of the module.
     * @param ?string $parent
     *     The parent module.
     * @param string[] $required
     *     The modules required by this module.
     * @param string[] $models
     *     The models associated to this module.
     * @param ?string $path
     *     Path to the root of the module. Might be useful.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $class,
        public readonly ?string $parent = null,
        public readonly array $required = [],
        public readonly array $models = [],
        public readonly ?string $path = null,
    ) {
    }
}
