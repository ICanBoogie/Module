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

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Define modules as services.
 */
class ModuleContainerExtension extends Extension
{
	/**
	 * Create a new instance.
	 *
	 * @param Application $app
	 *
	 * @return static
	 */
	static public function from(Application $app)
	{
		return new static($app);
	}

	/**
	 * @var Application
	 */
	private $app;

	/**
	 * @param Application $app
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * Loads a specific configuration.
	 *
	 * @param array $configs An array of configuration values
	 * @param ContainerBuilder $container A ContainerBuilder instance
	 *
	 * @throws \InvalidArgumentException When provided tag is not defined in this extension
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		foreach ($this->app->modules->descriptors as $module_id => $descriptor)
		{
			$class = $descriptor[Descriptor::CLASSNAME];

			$definition = (new Definition($class))
				->setFactory([ new Reference('modules'), 'offsetGet' ])
				->setArguments([ $module_id ]);

			$container->setDefinition("module.$module_id", $definition);

			$this->register_models($module_id, $descriptor[Descriptor::MODELS], $container);
		}
	}

	/**
	 * @param string $module_id
	 * @param array $models
	 * @param ContainerBuilder $container
	 */
	private function register_models($module_id, array $models, ContainerBuilder $container)
	{
		foreach ($models as $model_id => $definition)
		{
			if ($model_id === 'primary')
			{
				$model_id = $module_id;
			}
			else
			{
				$model_id = "$module_id/$model_id";
			}

			$class = $definition[Model::CLASSNAME];

			$definition = (new Definition($class))
				->setFactory([ new Reference('models'), 'offsetGet' ])
				->setArguments([ $model_id ]);

			$container->setDefinition("model.$model_id", $definition);
		}
	}
}
