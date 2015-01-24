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
use ICanBoogie\I18n;
use ICanBoogie\Module\Descriptor;
use ICanBoogie\Module\ModuleCollection;

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
 */
class Module extends Object
{
	/**
	 * Defines the category for the module.
	 *
	 * When modules are listed they are usually grouped by category. The category is also often
	 * used to create the main navigation menu of the admin interface.
	 *
	 * The category of the module is translated within the `module_category` scope.
	 *
	 * @var string
	 *
	 * @deprecated
	 */
	const T_CATEGORY = 'category';

	/**
	 * Defines the PHP class of the module.
	 *
	 * If the class is not defined it is resolved during indexing using the {@link T_NAMESPACE}
	 * tag and the following pattern : `<namespace>\Module`.
	 *
	 * @var string
	 *
	 * @deprecated
	 */
	const T_CLASS = 'class';

	/**
	 * Defines a short description of what the module do.
	 *
	 * @var string
	 *
	 * @deprecated
	 */
	const T_DESCRIPTION = 'description';

	/**
	 * Defines the state of the module.
	 *
	 * @var bool
	 *
	 * @deprecated
	 */
	const T_DISABLED = 'disabled';

	/**
	 * Defines the module that the module extends.
	 *
	 * @var string|\ICanBoogie\Module
	 *
	 * @deprecated
	 */
	const T_EXTENDS = 'extends';

	/**
	 * Defines the identifier of the module.
	 *
	 * If the identifier is not defined the name of the module directory is used instead.
	 *
	 * @var string
	 *
	 * @deprecated
	 */
	const T_ID = 'id';

	/**
	 * Defines the state of the module.
	 *
	 * Required modules are always enabled.
	 *
	 * @var bool
	 *
	 * @deprecated
	 */
	const T_REQUIRED = 'required';

	/**
	 * Defines the modules that the module requires.
	 *
	 * The required modules are defined using an array where each key/value pair is the identifier
	 * of the module and the minimum version required.
	 *
	 * @var array[string]string
	 *
	 * @deprecated
	 */
	const T_REQUIRES = 'requires';

	/**
	 * Defines the models of the module.
	 *
	 * @var array[string]array|string
	 *
	 * @deprecated
	 */
	const T_MODELS = 'models';

	/**
	 * Defines the namespace of the module.
	 *
	 * This attribute must be defined at construct time.
	 *
	 * @var string
	 *
	 * @deprecated
	 */
	const T_NAMESPACE = 'namespace';

	/**
	 * Path to the module's directory.
	 *
	 * This tag is resolved when the module is indexed.
	 *
	 * @var string
	 *
	 * @deprecated
	 */
	const T_PATH = 'path';

	/**
	 * General permission of the module.
	 *
	 * @var string|int
	 *
	 * @deprecated
	 */
	const T_PERMISSION = 'permission';

	/**
	 * Defines the permissions added by the module.
	 *
	 * @var array[]string
	 *
	 * @deprecated
	 */
	const T_PERMISSIONS = 'permissions';

	/**
	 * Defines the title of the module.
	 *
	 * The title of the module is translated within the `module_title` scope.
	 *
	 * @var string
	 *
	 * @deprecated
	 */
	const T_TITLE = 'title';

	/**
	 * Defines the version (and revision) of the module.
	 *
	 * @var string
	 *
	 * @deprecated
	 */
	const T_VERSION = 'version';

	/**
	 * Defines the weight of the module.
	 *
	 * The weight of the module is resolved during modules indexing according to the
	 * {@link T_EXTENDS} and {@link T_REQUIRES} tags.
	 *
	 * @var int
	 *
	 * @deprecated
	 */
	const T_WEIGHT = 'weight';

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
	const PERMISSION_NONE = 0;
	const PERMISSION_ACCESS = 1;
	const PERMISSION_CREATE = 2;
	const PERMISSION_MAINTAIN = 3;
	const PERMISSION_MANAGE = 4;
	const PERMISSION_ADMINISTER = 5;

	/**
	 * Defines the name of the operation used to save the records of the module.
	 *
	 * @var string
	 */
	const OPERATION_SAVE = 'save';

	/**
	 * Defines the name of the operation used to delete the records of the module.
	 *
	 * @var string
	 */
	const OPERATION_DELETE = 'delete';

	/**
	 * Returns the identifier of the module as defined by its descriptor.
	 *
	 * This method is the getter for the {@link $id} magic property.
	 *
	 * @return string
	 */
	protected function get_id()
	{
		return $this->descriptor[Descriptor::ID];
	}

	/**
	 * Returns the path of the module as defined by its descriptor.
	 *
	 * This method is the getter for the {@link $path} magic property.
	 *
	 * @return string
	 */
	protected function get_path()
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
	 *
	 * @return array
	 */
	protected function get_descriptor()
	{
		return $this->descriptor;
	}

	/**
	 * Constructor.
	 *
	 * Initializes the {@link $descriptor} property.
	 *
	 * @param array $descriptor
	 */
	public function __construct(array $descriptor)
	{
		$this->descriptor = $descriptor;
	}

	/**
	 * Returns the identifier of the module.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->id;
	}

	/**
	 * Returns the _flat_ version of the module's identifier.
	 *
	 * This method is the getter for the {@link $flat_id} magic property.
	 *
	 * @return string
	 */
	protected function get_flat_id()
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
	 *
	 * @return ActiveRecord\Model
	 */
	protected function get_model()
	{
		return $this->model();
	}

	/**
	 * Returns the module title, translated to the current language.
	 *
	 * @return string
	 */
	protected function get_title()
	{
		$default = isset($this->descriptor[Descriptor::TITLE]) ? $this->descriptor[Descriptor::TITLE] : 'Undefined';

		return I18n\t($this->flat_id, [], [ 'scope' => 'module_title', 'default' => $default ]);
	}

	/**
	 * Returns the parent module.
	 *
	 * @return Module|null
	 */
	protected function get_parent()
	{
		return $this->descriptor[Descriptor::INHERITS];
	}

	/**
	 * Checks if the module is installed.
	 *
	 * @param Errors $errors Error collection.
	 *
	 * @return mixed `true` if the module is installed, `false` if the module
	 * (or parts of) is not installed, `null` if the module has no installation.
	 */
	public function is_installed(Errors $errors)
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
				$errors[$this->id] = $errors->format("The model %name is not installed.", [

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
	 * @param Errors $errors Error collection.
	 *
	 * @return boolean|null true if the module has successfully been installed, false if the
	 * module (or parts of the module) fails to install or null if the module has
	 * no installation process.
	 */
	public function install(Errors $errors)
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

			if (!$model->install())
			{
				$errors[$this->id] = $errors->format('Unable to install model %model', [ '%model' => $name ]);

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
	 * @return boolean|null `true` if the module was successfully uninstalled. `false` if the module
	 * (or parts of the module) failed to uninstall. `null` if there is no uninstall process.
	 */
	public function uninstall()
	{
		if (empty($this->descriptor[Descriptor::MODELS]))
		{
			return;
		}

		$rc = true;

		foreach ($this->descriptor[Descriptor::MODELS] as $name => $tags)
		{
			$model = $this->model($name);

			if (!$model->is_installed())
			{
				continue;
			}

			if (!$model->uninstall())
			{
				$rc = false;
			}
		}

		return $rc;
	}

	/**
	 * Cache for loaded models.
	 *
	 * @var ActiveRecord\Model[]
	 */
	protected $models = [];

	/**
	 * Get a model from the module.
	 *
	 * If the model has not been created yet, it is created on the fly.
	 *
	 * @param string $which The identifier of the model to get.
	 *
	 * @return Model The requested model.
	 *
	 * @throws ModelNotDefined when the model is not defined by the module.
	 * @throws \RuntimeException when the class of the model does not exists.
	 */
	public function model($which='primary')
	{
		if (empty($this->models[$which]))
		{
			if (empty($this->descriptor[Descriptor::MODELS][$which]))
			{
				throw new ModelNotDefined($which);
			}

			#
			# resolve model tags
			#

			$callback = "resolve_{$which}_model_tags";

			if (!method_exists($this, $callback))
			{
				$callback = 'resolve_model_tags';
			}

			$attributes = $this->$callback($this->descriptor[Descriptor::MODELS][$which], $which);

			#
			# COMPAT WITH 'inherit'
			#

			if ($attributes instanceof Model)
			{
				$this->models[$which] = $attributes;

				return $attributes;
			}

			#
			# create model
			#

			$class = $attributes[Model::T_CLASS];

			if (!class_exists($class))
			{
				throw new \RuntimeException(\ICanBoogie\format("Unable to instantiate model %model, the class %class does not exists.", [

					'model' => "$this->id/$which",
					'class' => $class

				]));
			}

			$this->models[$which] = new $class($attributes);
		}

		#
		# return cached model
		#

		return $this->models[$which];
	}

	protected function resolve_model_tags($tags, $which)
	{
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

				$app = \ICanBoogie\app();

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

			$tags = [ Model::T_EXTENDS => $model_name ];
		}

		#
		# defaults
		#

		$id = $this->id;

		$tags += [

			Model::CONNECTION => 'primary',
			Model::ID => $which == 'primary' ? $id : $id . '/' . $which

		];

		if (empty($tags[Model::NAME]))
		{
			$tags[Model::NAME] = ModuleCollection::format_model_name($id, $which);
		}

		#
		# relations
		#

		if (isset($tags[Model::T_EXTENDS]))
		{
			$extends = &$tags[Model::T_EXTENDS];

			if (is_string($extends))
			{
				$extends = \ICanBoogie\app()->models[$extends];
			}

			if (!$tags[Model::T_CLASS])
			{
				$tags[Model::T_CLASS] = get_class($extends);
			}
		}

		#
		#
		#

		if (isset($tags[Model::T_IMPLEMENTS]))
		{
			$implements =& $tags[Model::T_IMPLEMENTS];

			foreach ($implements as &$implement)
			{
				if (isset($implement['model']))
				{
					list($implement_id, $implement_which) = explode('/', $implement['model']) + [ 1 => 'primary' ];

					if ($id == $implement_id && $which == $implement_which)
					{
						throw new \RuntimeException(\ICanBoogie\format('Model %module/%model implements itself !', [

							'%module' => $id,
							'%model' => $which

						]));
					}

					$module = ($implement_id == $id) ? $this : \ICanBoogie\app()->modules[$implement_id];

					$implement['table'] = $module->model($implement_which);
				}
				else if (is_string($implement['table']))
				{
					throw new \RuntimeException(\ICanBoogie\format('Model %model of module %module implements a table: %table', [

						'%model' => $which,
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
			$tags[Model::CONNECTION] = \ICanBoogie\app()->connections[$connection];
		}

		return $tags;
	}

	/**
	 * Get a block.
	 *
	 * @param string $name The name of the block to get.
	 *
	 * @return mixed Depends on the implementation. Should return a string or object that can be stringified.
	 *
	 * @throws \RuntimeException if the block is not defined.
	 */
	public function getBlock($name)
	{
		$args = func_get_args();

		array_shift($args);

		$callback = 'block_' . $name;

		if (!method_exists($this, $callback))
		{
			throw new \RuntimeException(\ICanBoogie\format('The %method method is missing from the %module module to create block %type.', [

				'%method' => $callback,
				'%module' => $this->id,
				'%type' => $name

			]));
		}

		return call_user_func_array([ $this, $callback ], $args);
	}
}
