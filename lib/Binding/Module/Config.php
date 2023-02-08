<?php

namespace ICanBoogie\Binding\Module;

use ICanBoogie\Module\Descriptor;

final class Config
{
    /**
     * @param array{ descriptors: array<string, array<Descriptor::*, mixed>> } $an_array
     */
    public static function __set_state(array $an_array): self
    {
        return new self(
            $an_array['descriptors'],
        );
    }

    /**
     * @param array<string, array<Descriptor::*, mixed>> $descriptors
     *     Where _key_ is a module identifier.
     */
    public function __construct(
        public readonly array $descriptors
    ) {
    }
}
