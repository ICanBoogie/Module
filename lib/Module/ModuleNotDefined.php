<?php

namespace ICanBoogie\Module;

use RuntimeException;
use Throwable;

use function ICanBoogie\format;

/**
 * Exception thrown when a requested module is not defined.
 *
 * @property-read string $module_id The identifier of the module that is not defined.
 */
final class ModuleNotDefined extends RuntimeException
{
    public function __construct(
        public readonly string $module_id,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            format('Module is not defined: %module_id', [ 'module_id' => $module_id ]),
            previous: $previous,
        );
    }
}
