<?php

namespace ICanBoogie\Module;

use ICanBoogie\Application;
use ICanBoogie\Binding\Module\Config;
use ICanBoogie\Binding\SymfonyDependencyInjection\ExtensionWithFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Define modules as services.
 */
final class ModuleContainerExtension extends Extension implements ExtensionWithFactory
{
    static public function from(Application $app): self
    {
        return new self($app);
    }

    private function __construct(
        private readonly Application $app
    ) {
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->app->config_for_class(Config::class);

        foreach ($config->descriptors as $id => $descriptor) {
            // descriptor
            $descriptor_id = "module_descriptor.$id";

            $definition = (new Definition(Descriptor::class))
                ->setFactory([ new Reference(Config::class), 'descriptor_for' ])
                ->setArguments([ $id ])
                ->addTag('module_descriptor', [ 'id' => $id ]);

            $container->setDefinition($descriptor_id, $definition);

            // module
            $class = $descriptor->class;

            $definition = (new Definition($class))
                ->setAutowired(true)
                ->setArgument('$descriptor', new Reference($descriptor_id))
                ->addTag('module', [ 'id' => $id ]);

            $container->setDefinition($class, $definition);
        }
    }
}
