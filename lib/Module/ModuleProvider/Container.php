<?php

namespace ICanBoogie\Module\ModuleProvider;

use ICanBoogie\Module;
use ICanBoogie\Module\ModuleProvider;
use Symfony\Contracts\Service\ServiceProviderInterface;
use Traversable;

use function array_combine;
use function array_keys;

final class Container implements ModuleProvider
{
    /**
     * @var string[]
     */
    private readonly array $ids;

    /**
     * @param ServiceProviderInterface<Module> $locator
     */
    public function __construct(
        private readonly ServiceProviderInterface $locator
    ) {
        $keys = array_keys($this->locator->getProvidedServices());
        $this->ids = array_combine($keys, $keys);
    }

    /**
     * @inheritdoc
     */
    public function module_for_id(string $id, string $class = null): Module
    {
        $service = $this->locator->get($id);

        if ($class) {
            assert($service instanceof $class);
        }

        // @phpstan-ignore-next-line
        return $service;
    }

    public function has_module(string $id): bool
    {
        return isset($this->ids[$id]);
    }

    public function getIterator(): Traversable
    {
        foreach ($this->ids as $id) {
            yield $id => fn(): Module => $this->locator->get($id);
        }
    }
}
