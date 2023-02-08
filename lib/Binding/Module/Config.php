<?php

namespace ICanBoogie\Binding\Module;

use ICanBoogie\Module\Descriptor;

final class Config
{
    /**
     * @param array{ descriptors: array<string, Descriptor> } $an_array
     */
    public static function __set_state(array $an_array): self
    {
        return new self(
            $an_array['descriptors'],
        );
    }

    /**
     * @param array<string, Descriptor> $descriptors
     *     Where _key_ is a module identifier.
     */
    public function __construct(
        public readonly array $descriptors
    ) {
    }

    public function descriptor_for(string $id): Descriptor
    {
        return $this->descriptors[$id];
    }
}
