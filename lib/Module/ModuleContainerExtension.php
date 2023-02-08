<?php

namespace ICanBoogie\Module;

use ICanBoogie\Application;
use ICanBoogie\Binding\Module\Config;
use ICanBoogie\Binding\SymfonyDependencyInjection\ExtensionWithFactory;
use InvalidArgumentException;
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

	/**
	 * Loads a specific configuration.
	 *
	 * @param array $configs An array of configuration values
	 * @param ContainerBuilder $container A ContainerBuilder instance
	 *
	 * @throws InvalidArgumentException When provided tag is not defined in this extension
	 */
	public function load(array $configs, ContainerBuilder $container): void
	{
        $config = $this->app->config_for_class(Config::class);

		foreach ($config->descriptors as $module_id => $descriptor)
		{
			$class = $descriptor[Descriptor::CLASSNAME];

			$definition = (new Definition($class))
				->setFactory([ new Reference(ModuleCollection::class), 'offsetGet' ])
				->setArguments([ $module_id ]);

			$container->setDefinition($class, $definition);
			$container->setAlias("module.$module_id", $class)->setPublic(true);
		}
	}
}
