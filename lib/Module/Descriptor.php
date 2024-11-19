<?php

namespace ICanBoogie\Module;

use ICanBoogie\Module;

/**
 * Module descriptor options.
 *
 * @package ICanBoogie\Module
 */
final readonly class Descriptor
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
     *     The models associated with this module.
     * @param ?string $path
     *     Path to the root of the module. Might be useful.
     */
    public function __construct(
        public string $id,
        public string $class,
        public ?string $parent = null,
        public array $required = [],
        public array $models = [],
        public ?string $path = null,
    ) {
    }
}
