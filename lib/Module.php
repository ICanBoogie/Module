<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie;

use ICanBoogie\ActiveRecord\Connection;
use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\ActiveRecord\ModelNotDefined;
use ICanBoogie\Module\Descriptor;
use ICanBoogie\Module\ModuleCollection;
use RuntimeException;
use Throwable;

/**
 * A module of the framework.
 *
 * @property-read array $descriptor The descriptor of the module.
 * @property-read string $flat_id Underscored identifier.
 * @property-read string $id The identifier of the module, defined by {@link Descriptor::ID}.
 * @property-read Model $model The primary model of the module.
 * @property-read Module $parent The parent module, defined by {@link Descriptor::INHERITS}.
 * @property-read string $path The path to the module, defined by {@link Descriptor::PATH}.
 * @property-read string $title The localized title of the module.
 * @property-read ModuleCollection $collection
 * @property-read Application $app
 */
class Module extends Prototyped
{
	/*
	 * PERMISSIONS:
	 *
	 * NONE: Well, you can't do anything
	 *
	 * ACCESS: You can access the module and view its records
	 *
	 * CREATE: You can create new records
	 *
	 * MAINTAIN: You can edit the records you created
	 *
	 * MANAGE: You can delete the records you created
	 *
	 * ADMINISTER: You have complete control over the module
	 *
	 */
	public const PERMISSION_NONE = 0;
	public const PERMISSION_ACCESS = 1;
	public const PERMISSION_CREATE = 2;
	public const PERMISSION_MAINTAIN = 3;
	public const PERMISSION_MANAGE = 4;
	public const PERMISSION_ADMINISTER = 5;

	/**
	 * Defines the name of the operation used to save the records of the module.
	 */
	public const OPERATION_SAVE = 'save';

	/**
	 * Defines the name of the operation used to delete the records of the module.
	 */
	public const OPERATION_DELETE = 'delete';

	/**
	 * Returns the identifier of the module as defined by its descriptor.
	 *
	 * This method is the getter for the {@link $id} magic property.
	 */
	protected function get_id(): string
	{
		return $this->descriptor[Descriptor::ID];
	}

	/**
	 * Returns the path of the module as defined by its descriptor.
	 *
	 * This method is the getter for the {@link $path} magic property.
	 */
	protected function get_path(): string
	{
		return $this->descriptor[Descriptor::PATH];
	}

	/**
	 * The descriptor of the module.
	 *
	 * @var array
	 */
	protected $descriptor;

	/**
	 * Returns the descriptor of the module.
	 *
	 * This method is the getter for the {@link $descriptor} magic property.
	 */
	protected function get_descriptor(): array
	{
		return $this->descriptor;
	}

	/**
	 * Cache for loaded models.
	 *
	 * @var ActiveRecord\Model[]
	 */
	private $models = [];

	/**
	 * @var ModuleCollection
	 */
	private $collection;

	protected function get_collection(): ModuleCollection
	{
		return $this->collection;
	}

	public function __construct(ModuleCollection $collection, array $descriptor)
	{
		$this->collection = $collection;
		$this->descriptor = $descriptor;
	}

	/**
	 * Returns the identifier of the module.
	 */
	public function __toString(): string
	{
		return $this->id;
	}

	/**
	 * Returns the _flat_ version of the module's identifier.
	 *
	 * This method is the getter for the {@link $flat_id} magic property.
	 */
	protected function get_flat_id(): string
	{
		return strtr($this->id, [

			'.' => '_',
			'-' => '_'

		]);
	}

	/**
	 * Returns the primary model of the module.
	 *
	 * This is the getter for the {@link $model} magic property.
	 */
	protected function get_model(): ActiveRecord\Model
	{
		return $this->model();
	}

	/**
	 * Returns the module title, translated to the current language.
	 *
	 * @deprecated
	 */
	protected function get_title(): string
	{
		$default = isset($this->descriptor[Descriptor::TITLE]) ? $this->descriptor[Descriptor::TITLE] : 'Undefined';

		return $this->app->translate($this->flat_id, [], [ 'scope' => 'module_title', 'default' => $default ]);
	}

	/**
	 * Returns the parent module.
	 */
	protected function get_parent(): ?Module
	{
		return $this->descriptor[Descriptor::INHERITS];
	}

	/**
	 * Checks if the module is installed.
	 *
	 * @return mixed `true` if the module is installed, `false` if the module
	 * (or parts of) is not installed, `null` if the module has no installation.
	 */
	public function is_installed(ErrorCollection $errors): ?bool
	{
		if (empty($this->descriptor[Descriptor::MODELS]))
		{
			return null;
		}

		$rc = true;

		foreach ($this->descriptor[Descriptor::MODELS] as $name => $tags)
		{
			if (!$this->model($name)->is_installed())
			{
				$errors->add($this->id, "The model %name is not installed.", [

					'name' => $name

				]);

				$rc = false;
			}
		}

		return $rc;
	}

	/**
	 * Install the module.
	 *
	 * If the module has models they are installed.
	 *
	 * @return bool|null true if the module has successfully been installed, false if the
	 * module (or parts of the module) fails to install or null if the module has
	 * no installation process.
	 */
	public function install(ErrorCollection $errors): ?bool
	{
		if (empty($this->descriptor[Descriptor::MODELS]))
		{
			return null;
		}

		$rc = true;

		foreach ($this->descriptor[Descriptor::MODELS] as $name => $tags)
		{
			$model = $this->model($name);

			if ($model->is_installed())
			{
				continue;
			}

			try
			{
				$model->install();
			}
			catch (Throwable $e)
			{
				$errors->add($this->id, "Unable to install model %model: !message", [

					'model' => $name,
					'message' => $e->getMessage()

				]);

				$rc = false;
			}
		}

		return $rc;
	}

	/**
	 * Uninstall the module.
	 *
	 * Basically it uninstall the models installed by the module.
	 *
	 * @return bool|null `true` if the module was successfully uninstalled. `false` if the module
	 * (or parts of the module) failed to uninstall. `null` if there is no uninstall process.
	 *
	 * @throws Throwable
	 */
	public function uninstall(): ?bool
	{
		if (empty($this->descriptor[Descriptor::MODELS]))
		{
			return null;
		}

		$rc = true;

		foreach ($this->descriptor[Descriptor::MODELS] as $name => $tags)
		{
			$model = $this->model($name);

			if (!$model->is_installed())
			{
				continue;
			}

			$model->uninstall();
		}

		return $rc;
	}

	/**
	 * Get a model from the module.
	 *
	 * If the model has not been created yet, it is created on the fly.
	 *
	 * @throws ModelNotDefined when the model is not defined by the module.
	 * @throws RuntimeException when the class of the model does not exists.
	 */
	public function model(string $model_id = 'primary'): Model
	{
		if (empty($this->models[$model_id]))
		{
			if (empty($this->descriptor[Descriptor::MODELS][$model_id]))
			{
				throw new ModelNotDefined($model_id);
			}

			#
			# resolve model tags
			#

			$callback = "resolve_{$model_id}_model_tags";

			if (!method_exists($this, $callback))
			{
				$callback = 'resolve_model_tags';
			}

			$attributes = $this->$callback($this->descriptor[Descriptor::MODELS][$model_id], $model_id);

			#
			# COMPATIBILITY WITH 'inherit'
			#

			if ($attributes instanceof Model)
			{
				$this->models[$model_id] = $attributes;

				return $attributes;
			}

			#
			# create model
			#

			$class = $attributes[Model::CLASSNAME];

			if (!class_exists($class))
			{
				throw new RuntimeException(format("Unable to instantiate model %model, the class %class does not exists.", [

					'model' => "$this->id/$model_id",
					'class' => $class

				]));
			}

			$this->models[$model_id] = new $class($this->app->models, $attributes);
		}

		#
		# return cached model
		#

		return $this->models[$model_id];
	}

	/**
	 * Resolves model tags.
	 *
	 * @param array|string $tags
	 */
	protected function resolve_model_tags($tags, string $model_id): array
	{
		$app = $this->app;

		#
		# The model may use another model, in which case the model to use is defined using a
		# string e.g. 'contents' or 'terms/nodes'
		#

		if (is_string($tags))
		{
			$model_name = $tags;

			if ($model_name == 'inherit')
			{
				$class = get_parent_class($this);

				foreach ($app->modules->descriptors as $module_id => $descriptor)
				{
					if ($class != $descriptor['class'])
					{
						continue;
					}

					$model_name = $app->models[$module_id];

					break;
				}
			}

			$tags = [ Model::EXTENDING => $model_name ];
		}

		#
		# defaults
		#

		$id = $this->id;

		$tags += [

			Model::CONNECTION => 'primary',
			Model::ID => $model_id == 'primary' ? $id : $id . '/' . $model_id,
			Model::EXTENDING => null

		];

		if (empty($tags[Model::NAME]))
		{
			$tags[Model::NAME] = ModuleCollection::format_model_name($id, $model_id);
		}

		#
		# relations
		#

		if (isset($tags[Model::EXTENDING]))
		{
			$extends = &$tags[Model::EXTENDING];

			if (is_string($extends))
			{
				$extends = $this->app->models[$extends];
			}

			if (!$tags[Model::CLASSNAME])
			{
				$tags[Model::CLASSNAME] = get_class($extends);
			}
		}

		#
		#
		#

		if (isset($tags[Model::IMPLEMENTING]))
		{
			$implements =& $tags[Model::IMPLEMENTING];

			foreach ($implements as &$implement)
			{
				if (isset($implement['model']))
				{
					list($implement_id, $implement_which) = explode('/', $implement['model']) + [ 1 => 'primary' ];

					if ($id == $implement_id && $model_id == $implement_which)
					{
						throw new RuntimeException(format('Model %module/%model implements itself !', [

							'%module' => $id,
							'%model' => $model_id

						]));
					}

					$module = ($implement_id == $id) ? $this : app()->modules[$implement_id];

					$implement['table'] = $module->model($implement_which);
				}
				else if (is_string($implement['table']))
				{
					throw new RuntimeException(format('Model %model of module %module implements a table: %table', [

						'%model' => $model_id,
						'%module' => $id,
						'%table' => $implement['table']

					]));
				}
			}
		}

		#
		# default class, if none was defined.
		#

		if (empty($tags[Model::CLASSNAME]))
		{
			$tags[Model::CLASSNAME] = 'ICanBoogie\ActiveRecord\Model';
		}

		#
		# connection
		#

		$connection = $tags[Model::CONNECTION];

		if (!($connection instanceof Connection))
		{
			$tags[Model::CONNECTION] = $this->app->connections[$connection];
		}

		return $tags;
	}

	/**
	 * Get a block.
	 *
	 * @return mixed Depends on the implementation. Should return a string or an object
	 * implementing `__toString`.
	 *
	 * @throws RuntimeException if the block is not defined.
	 */
	public function getBlock(string $name)
	{
		$args = func_get_args();

		array_shift($args);

		$callback = 'block_' . $name;

		if (!method_exists($this, $callback))
		{
			throw new RuntimeException(format('The %method method is missing from the %module module to create block %type.', [

				'%method' => $callback,
				'%module' => $this->id,
				'%type' => $name

			]));
		}

		return $this->$callback(...$args);
	}
}
