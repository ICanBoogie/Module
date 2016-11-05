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

use ICanBoogie\Accessor\AccessorTrait;
use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\ErrorCollection;
use ICanBoogie\Module;
use ICanBoogie\Storage\Storage;
use ICanBoogie\Module\ModuleCollection\InstallableModulesFilter;

use function ICanBoogie\format;
use function ICanBoogie\camelize;
use function ICanBoogie\singularize;
use function ICanBoogie\stable_sort;

/**
 * A module collection.
 *
 * @property-read array $config_paths Paths of the enabled modules having a `config` directory.
 * @property-read array $locale_paths Paths of the enabled modules having a `locale` directory.
 * @property-read array $disabled_modules_descriptors Descriptors of the disabled modules.
 * @property-read array $enabled_modules_descriptors Descriptors of the enabled modules.
 * @property-read array $index Index for the modules.
 */
class ModuleCollection implements \ArrayAccess, \IteratorAggregate
{
	use AccessorTrait;

	/**
	 * May be used with the {@link filter_descriptors_by_users()} method to filter the descriptors
	 * of enabled modules.
	 */
	const ONLY_ENABLED_MODULES = false;

	/**
	 * May be used with the {@link filter_descriptors_by_users()} method to filter the descriptors
	 * of all modules, enabled or not.
	 */
	const ALL_MODULES = true;

	/**
	 * Formats a SQL table name given the module id and the model id.
	 *
	 * @param string $module_id
	 * @param string $model_id
	 *
	 * @return string
	 */
	static public function format_model_name($module_id, $model_id = 'primary')
	{
		return preg_replace('#[^0-9a-zA-Z$_]#', '_', $module_id) . ($model_id == 'primary' ? '' : '__' . $model_id);
	}

	/**
	 * The descriptors for the modules.
	 *
	 * @var array
	 */
	public $descriptors = [];

	/**
	 * The paths where modules can be found.
	 *
	 * @var array
	 */
	protected $paths = [];

	/**
	 * A cache for the modules index.
	 *
	 * @var Storage
	 */
	protected $cache;

	/**
	 * Instantiated modules.
	 *
	 * @var Module[]
	 */
	protected $modules = [];

	/**
	 * The index for the available modules is created with the accessor object.
	 *
	 * @param array $paths The paths to look for modules.
	 * @param Storage $cache The cache to use for the module indexes.
	 */
	public function __construct($paths, Storage $cache = null)
	{
		$this->paths = $paths;
		$this->cache = $cache;
	}

	/**
	 * Revokes constructions.
	 *
	 * The following properties are revoked:
	 *
	 * - {@link $enabled_modules_descriptors}
	 * - {@link $disabled_modules_descriptors}
	 * - {@link $catalog_paths}
	 * - {@link $config_paths}
	 *
	 * The method is usually invoked when modules state changes, in order to reflect these
	 * changes.
	 */
	protected function revoke_constructions()
	{
		unset($this->enabled_modules_descriptors);
		unset($this->disabled_modules_descriptors);
		unset($this->catalog_paths);
		unset($this->config_paths);
	}

	/**
	 * Enables a module.
	 *
	 * @param string $module_id Module identifier.
	 */
	public function enable($module_id)
	{
		$this->change_module_availability($module_id, false);
	}

	/**
	 * Disables a module.
	 *
	 * @param string $module_id Module identifier.
	 */
	public function disable($module_id)
	{
		$this->change_module_availability($module_id, true);
	}

	/**
	 * Used to enable or disable a module using the specified offset as a module identifier.
	 *
	 * @param string $module_id Identifier of the module.
	 * @param bool $enable Status of the module: `true` for enabled, `false` for disabled.
	 */
	public function offsetSet($module_id, $enable)
	{
		$this->change_module_availability($module_id, $enable);
	}

	/**
	 * Disables a module by setting the {@link Descriptor::DISABLED} key of its descriptor to `true`.
	 *
	 * @param string $module_id Module identifier.
	 */
	public function offsetUnset($module_id)
	{
		$this->change_module_availability($module_id, false);
	}

	/**
	 * Checks the availability of a module.
	 *
	 * A module is considered available when its descriptor is defined, and the
	 * {@link Descriptor::DISABLED} key of its descriptor is empty.
	 *
	 * Note: `empty()` will call {@link offsetGet()} to check if the value is not empty. So, unless
	 * you want to use the module you check, better check using `!isset()`, otherwise the module
	 * you check is loaded too.
	 *
	 * @param string $module_id Module identifier.
	 *
	 * @return boolean Whether or not the module is available.
	 */
	public function offsetExists($module_id)
	{
		$this->ensure_modules_are_indexed();

		$descriptors = $this->descriptors;

		return isset($descriptors[$module_id])
		&& empty($descriptors[$module_id][Descriptor::DISABLED]);
	}

	/**
	 * Returns a module object.
	 *
	 * If the {@link autorun} property is `true`, the {@link Module::run()} method of the module
	 * is invoked upon its first loading.
	 *
	 * @param string $module_id Module identifier.
	 *
	 * @throws ModuleNotDefined when the requested module is not defined.
	 *
	 * @throws ModuleIsDisabled when the module is disabled.
	 *
	 * @throws ModuleConstructorMissing when the class that should be used to create its instance
	 * is not defined.
	 *
	 * @return Module
	 */
	public function offsetGet($module_id)
	{
		$this->ensure_modules_are_indexed();

		if (isset($this->modules[$module_id]))
		{
			return $this->modules[$module_id];
		}

		return $this->modules[$module_id] = $this->instantiate_module($module_id);
	}

	/**
	 * Returns an iterator for instantiated modules.
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		$this->ensure_modules_are_indexed();

		return new \ArrayIterator($this->modules);
	}

	/**
	 * Filter descriptors.
	 *
	 * @param callable $filter
	 *
	 * @return array
	 */
	public function filter_descriptors(callable $filter)
	{
		return array_filter($this->descriptors, $filter);
	}

	/**
	 * Returns the modules using a module.
	 *
	 * @param string $module_id Used module identifier.
	 * @param bool $all One of {@link ONLY_ENABLED_MODULES} or {@link ALL_MODULES}.
	 * Default: {@link ONLY_ENABLED_MODULES}.
	 *
	 * @return array A array of filtered descriptors.
	 */
	public function filter_descriptors_by_users($module_id, $all = self::ONLY_ENABLED_MODULES)
	{
		$users = [];
		$descriptors = $all ? $this->descriptors : $this->enabled_modules_descriptors;

		foreach ($descriptors as $user_id => $descriptor)
		{
			if ($descriptor[Descriptor::INHERITS] == $module_id
			|| in_array($module_id, $descriptor[Descriptor::REQUIRES]))
			{
				$users[$user_id] = $descriptor;
			}
		}

		return $users;
	}

	/**
	 * Indexes the modules found in the paths specified during construct.
	 *
	 * The index is made of an array of descriptors, an array of catalogs paths, an array of
	 * configs path, and finally an array of configs constructors.
	 *
	 * The method also creates a `DIR` constant for each module. The constant is defined in the
	 * namespace of the module e.g. `Icybee\ModuleCollection\Nodes\DIR`.
	 *
	 * @return array
	 */
	protected function lazy_get_index()
	{
		$index = $this->obtain_index();
		$this->descriptors = $descriptors = $index['descriptors'];
		$this->define_constants($descriptors);

		return $index;
	}

	/**
	 * Obtain index either from cache or by building it.
	 *
	 * @return array|mixed|null
	 */
	private function obtain_index()
	{
		$cache = $this->cache;

		if ($cache)
		{
			$key = 'cached_modules_' . substr(sha1(implode('#', $this->paths)), 0, 8);
			$index = $cache->retrieve($key);

			if ($index)
			{
				return $index;
			}

			$index = $this->index_modules();
			$cache->store($key, $index);

			return $index;
		}

		return $this->index_modules();
	}

	/**
	 * Construct the index for the modules.
	 *
	 * The index contains the following values:
	 *
	 * - (array) descriptors: The descriptors of the modules, ordered by weight.
	 * - (array) catalogs: Absolute paths to locale catalog directories.
	 * - (array) configs: Absolute paths to config directories.
	 * - (array) classes aliases: An array of _key/value_ pairs where _key_ is the alias of a class
	 * and _value_ if the real class.
	 *
	 * @return array
	 */
	protected function index_modules()
	{
		$descriptors = $this->paths ? $this->index_descriptors($this->paths) : [];
		$catalogs = [];
		$configs = [];

		foreach ($descriptors as $id => $descriptor)
		{
			$path = $descriptor[Descriptor::PATH];

			if ($descriptor['__has_locale'])
			{
				$catalogs[] = $path . 'locale';
			}

			if ($descriptor['__has_config'])
			{
				$configs[] = $path . 'config';
			}
		}

		return [

			'descriptors' => $descriptors,
			'catalogs' => $catalogs,
			'configs' => $configs

		];
	}

	/**
	 * Indexes descriptors.
	 *
	 * The descriptors are extended with the following default values:
	 *
	 * - (string) category: null.
	 * - (string) class: ModuleCollection\<normalized_module_part>
	 * - (string) description: null.
	 * - (bool) disabled: false if required, true otherwise.
	 * - (string) extends: null.
	 * - (string) id: The module's identifier.
	 * - (array) models: Empty array.
	 * - (string) path: The absolute path to the module directory.
	 * - (string) permission: null.
	 * - (array) permissions: Empty array.
	 * - (bool) startup: false.
	 * - (bool) required: false.
	 * - (array) requires: Empty array.
	 * - (string) weight: 0.
	 *
	 * The descriptors are ordered according to their inheritance and weight.
	 *
	 * @param array $paths
	 *
	 * @return array
	 */
	protected function index_descriptors(array $paths)
	{
		$descriptors = $this->collect_descriptors($paths);

		if (!$descriptors)
		{
			return [];
		}

		#
		# Compute inheritance.
		#

		$find_parents = function($id, &$parents = []) use (&$find_parents, &$descriptors)
		{
			$parent = $descriptors[$id][Descriptor::INHERITS];

			if ($parent)
			{
				$parents[] = $parent;

				$find_parents($parent, $parents);
			}

			return $parents;
		};

		foreach ($descriptors as $id => &$descriptor)
		{
			$descriptor['__parents'] = $find_parents($id);
		}

		#
		# Orders descriptors according to their weight.
		#

		$ordered_ids = $this->order_ids(array_keys($descriptors), $descriptors);
		$descriptors = array_merge(array_combine($ordered_ids, $ordered_ids), $descriptors);

		foreach ($descriptors as $id => &$descriptor)
		{
			foreach ($descriptor[Descriptor::MODELS] as $model_id => &$model_descriptor)
			{
				if ($model_descriptor != 'inherit')
				{
					continue;
				}

				$parent_descriptor = $descriptors[$descriptor[Descriptor::INHERITS]];
				$model_descriptor = [

					Model::EXTENDING => $parent_descriptor[Descriptor::ID] . '/' . $model_id

				];
			}

			$descriptor = $this->alter_descriptor($descriptor);
		}

		return $descriptors;
	}

	/**
	 * Collects descriptors from paths.
	 *
	 * @param array $paths
	 *
	 * @return array
	 */
	protected function collect_descriptors(array $paths)
	{
		$descriptors = [];

		foreach ($paths as $root)
		{
			$root = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			$descriptor_path = $root . 'descriptor.php';

			if (file_exists($descriptor_path))
			{
				$id = basename(realpath($root));
				$descriptor = $this->read_descriptor($id, $root);

				$descriptors[$descriptor[Descriptor::ID]] = $descriptor;
			}
			else
			{
				try
				{
					$dir = new \DirectoryIterator($root);
				}
				catch (\Exception $e)
				{
					throw new \RuntimeException(format('Unable to open directory %root', [

						'root' => $root

					]));
				}

				foreach ($dir as $file)
				{
					if ($file->isDot() || !$file->isDir())
					{
						continue;
					}

					$id = $file->getFilename();
					$path = $root . $id . DIRECTORY_SEPARATOR;
					$descriptor = $this->read_descriptor($id, $path);

					$descriptors[$descriptor[Descriptor::ID]] = $descriptor;
				}
			}
		}

		return $descriptors;
	}

	/**
	 * Reads the descriptor file.
	 *
	 * The descriptor file is extended with private values and default values.
	 *
	 * @param string $module_id The identifier of the module.
	 * @param string $path The path to the directory where the descriptor is located.
	 *
	 * @throws \InvalidArgumentException in the following situations:
	 * - The descriptor is not an array
	 * - The {@link Descriptor::TITLE} key is empty.
	 * - The {@link Descriptor::NS} key is empty.
	 *
	 * @return array
	 */
	protected function read_descriptor($module_id, $path)
	{
		$descriptor_path = $path . 'descriptor.php';
		$descriptor = require $descriptor_path;

		if (!is_array($descriptor))
		{
			throw new \InvalidArgumentException(format
			(
				'%var should be an array: %type given instead in %path', [

					'var' => 'descriptor',
					'type' => gettype($descriptor),
					'path' => $descriptor_path

				]
			));
		}

		if (empty($descriptor[Descriptor::TITLE]))
		{
			throw new \InvalidArgumentException(format
			(
				'The %name value of the %id module descriptor is empty in %path.', [

					'name' => Descriptor::TITLE,
					'id' => $module_id,
					'path' => $descriptor_path

				]
			));
		}

		if (empty($descriptor[Descriptor::NS]))
		{
			throw new \InvalidArgumentException(format
			(
				'%name is required. Invalid descriptor for module %id in %path.', [

					'name' => Descriptor::NS,
					'id' => $module_id,
					'path' => $descriptor_path

				]
			));
		}

		return Descriptor::normalize($descriptor + [

			Descriptor::ID => $module_id,
			Descriptor::PATH => $path,

			'__has_config' => is_dir($path . 'config'),
			'__has_locale' => is_dir($path . 'locale'),
			'__parents' => []

		]);
	}

	/**
	 * Alters the module descriptor.
	 *
	 * @param array $descriptor Descriptor of the module to index.
	 *
	 * @return array The altered descriptor.
	 */
	protected function alter_descriptor(array $descriptor)
	{
		$id = $descriptor[Descriptor::ID];
		$namespace = $descriptor[Descriptor::NS];

		# models and active records

		foreach ($descriptor[Descriptor::MODELS] as $model_id => &$definition)
		{
			if (!is_array($definition))
			{
				throw new \InvalidArgumentException(format('Model definition must be array, given: %value.', [

					'value' => $definition

				]));
			}

			$basename = $id;
			$separator_position = strrpos($basename, '.');

			if ($separator_position)
			{
				$basename = substr($basename, $separator_position + 1);
			}

			if (empty($definition[Model::NAME]))
			{
				$definition[Model::NAME] = self::format_model_name($id, $model_id);
			}

			if (empty($definition[Model::ACTIVERECORD_CLASS]))
			{
				$definition[Model::ACTIVERECORD_CLASS] = $namespace . '\\' . camelize(singularize($model_id == 'primary' ? $basename : $model_id));
			}

			if (empty($definition[Model::CLASSNAME]))
			{
				$definition[Model::CLASSNAME] = $definition[Model::ACTIVERECORD_CLASS] . 'Model';
			}
		}

		return $descriptor;
	}

	/**
	 * Traverses the descriptors and create two array of descriptors: one for the disabled modules
	 * and the other for enabled modules. The {@link $disabled_modules_descriptors} magic property
	 * receives the descriptors of the disabled modules, while the {@link $enabled_modules_descriptors}
	 * magic property receives the descriptors of the enabled modules.
	 */
	private function sort_modules_descriptors()
	{
		$enabled = [];
		$disabled = [];

		$this->ensure_modules_are_indexed();

		foreach ($this->descriptors as $module_id => &$descriptor)
		{
			if (isset($this[$module_id]))
			{
				$enabled[$module_id] = $descriptor;
			}
			else
			{
				$disabled[$module_id] = $descriptor;
			}
		}

		$this->enabled_modules_descriptors = $enabled;
		$this->disabled_modules_descriptors = $disabled;
	}

	/**
	 * Returns the descriptors of the disabled modules.
	 *
	 * This method is the getter for the {@link $disabled_modules_descriptors} magic property.
	 *
	 * @return array
	 */
	protected function lazy_get_disabled_modules_descriptors()
	{
		$this->sort_modules_descriptors();

		return $this->disabled_modules_descriptors;
	}

	/**
	 * Returns the descriptors of the enabled modules.
	 *
	 * This method is the getter for the {@link $enabled_modules_descriptors} magic property.
	 *
	 * @return array
	 */
	protected function lazy_get_enabled_modules_descriptors()
	{
		$this->sort_modules_descriptors();

		return $this->enabled_modules_descriptors;
	}

	/**
	 * Returns the paths of the enabled modules which have a `locale` folder.
	 *
	 * @return array
	 */
	protected function lazy_get_locale_paths()
	{
		$paths = [];

		foreach ($this->enabled_modules_descriptors as $module_id => $descriptor)
		{
			if (!$descriptor['__has_locale'])
			{
				continue;
			}

			$paths[] = $descriptor[Descriptor::PATH] . 'locale';
		}

		return $paths;
	}

	/**
	 * Returns the paths of the enabled modules which have a `config` folder.
	 *
	 * @return array
	 */
	protected function lazy_get_config_paths()
	{
		$paths = [];

		foreach ($this->enabled_modules_descriptors as $module_id => $descriptor)
		{
			if (!$descriptor['__has_config'])
			{
				continue;
			}

			$paths[$descriptor[Descriptor::PATH] . 'config'] = 0;
		}

		return $paths;
	}

	/**
	 * Orders the module ids provided according to module inheritance and weight.
	 *
	 * @param array $ids The module ids to order.
	 * @param array $descriptors Module descriptors.
	 *
	 * @return array
	 */
	public function order_ids(array $ids, array $descriptors = null)
	{
		$ordered = [];
		$extends_weight = [];

		if ($descriptors === null)
		{
			$descriptors = $this->descriptors;
		}

		$count_extends = function($super_id) use (&$count_extends, &$descriptors)
		{
			$i = 0;

			foreach ($descriptors as $module_id => $descriptor)
			{
				if ($descriptor[Descriptor::INHERITS] !== $super_id)
				{
					continue;
				}

				$i += 1 + $count_extends($module_id);
			}

			return $i;
		};

		$count_required = function($required_id) use (&$descriptors, &$extends_weight)
		{
			$i = 0;

			foreach ($descriptors as $module_id => $descriptor)
			{
				if (!in_array($required_id, $descriptor[Descriptor::REQUIRES]))
				{
					continue;
				}

				$i += 1 + $extends_weight[$module_id];
			}

			return $i;
		};

		foreach ($ids as $module_id)
		{
			$extends_weight[$module_id] = $count_extends($module_id);
		}

		foreach ($ids as $module_id)
		{
 			$ordered[$module_id] = -$extends_weight[$module_id] -$count_required($module_id) + $descriptors[$module_id][Descriptor::WEIGHT];
		}

		stable_sort($ordered);

		return array_keys($ordered);
	}

	/**
	 * Returns the usage of a module by other modules.
	 *
	 * @param string $module_id The identifier of the module.
	 * @param bool $all One of {@link ONLY_ENABLED_MODULES} or {@link ALL_MODULES}.
	 * Default: {@link ONLY_ENABLED_MODULES}.
	 *
	 * @return int
	 */
	public function usage($module_id, $all = self::ONLY_ENABLED_MODULES)
	{
		return count($this->filter_descriptors_by_users($module_id, $all));
	}

	/**
	 * Checks if a module inherits from another.
	 *
	 * @param string $module_id Module identifier.
	 * @param string $parent_id Identifier of the parent module.
	 *
	 * @return boolean `true` if the module inherits from the other.
	 */
	public function is_inheriting($module_id, $parent_id)
	{
		while ($module_id)
		{
			if ($module_id == $parent_id)
			{
				return true;
			}

			$descriptor = $this->descriptors[$module_id];

			$module_id = empty($descriptor[Descriptor::INHERITS]) ? null : $descriptor[Descriptor::INHERITS];
		}

		return false;
	}

	/**
	 * Install all the enabled modules.
	 *
	 * @param ErrorCollection|null $errors
	 *
	 * @return ErrorCollection
	 *
	 * @throws ModuleCollectionInstallFailed if an error occurs.
	 */
	public function install(ErrorCollection $errors = null)
	{
		if (!$errors)
		{
			$errors = new ErrorCollection;
		}

		foreach (array_keys($this->filter_descriptors(new InstallableModulesFilter($this))) as $module_id)
		{
			try
			{
				$this[$module_id]->install($errors);
			}
			catch (\Exception $e)
			{
				$errors[$module_id] = $e;
			}
		}

		if ($errors->count())
		{
			throw new ModuleCollectionInstallFailed($errors);
		}

		return $errors;
	}

	/**
	 * Resolves a class name using module inheritance.
	 *
	 * To resolve a given class name, the method checks in each module namespace—starting from the
	 * specified module—if the class exists. If it does, it returns its fully qualified name.
	 *
	 * @param string $unqualified_classname
	 * @param string|Module $module_id
	 * @param array $tried
	 *
	 * @return string|false The resolved file name, or `false` if it could not be resolved.
	 *
	 * @throws ModuleNotDefined if the specified module, or the module specified by
	 * {@link Descriptor::INHERITS} is not defined.
	 */
	public function resolve_classname($unqualified_classname, $module_id, array &$tried = [])
	{
		if ($module_id instanceof Module)
		{
			$module_id = $module_id->id;
		}

		while ($module_id)
		{
			$this->assert_module_is_defined($module_id);

			$descriptor = $this->descriptors[$module_id];
			$fully_qualified_classname = $descriptor[Descriptor::NS] . '\\' . $unqualified_classname;
			$tried[] = $fully_qualified_classname;

			if (class_exists($fully_qualified_classname, true))
			{
				return $fully_qualified_classname;
			}

			$module_id = $descriptor[Descriptor::INHERITS];
		}

		return false;
	}

	/**
	 * Changes a module availability.
	 *
	 * @param string $module_id
	 * @param bool $available
	 */
	protected function change_module_availability($module_id, $available)
	{
		$this->ensure_modules_are_indexed();

		if (empty($this->descriptors[$module_id]))
		{
			return;
		}

		$this->descriptors[$module_id][Descriptor::DISABLED] = $available;
		$this->revoke_constructions();
	}

	/**
	 * Ensures that modules are indexed, index them if not.
	 */
	protected function ensure_modules_are_indexed()
	{
		$this->index;
	}

	/**
	 * Asserts that a module is defined.
	 *
	 * @param string $module_id Module identifier.
	 *
	 * @throws ModuleNotDefined if the module is not defined.
	 */
	protected function assert_module_is_defined($module_id)
	{
		if (empty($this->descriptors[$module_id]))
		{
			throw new ModuleNotDefined($module_id);
		}
	}

	/**
	 * Asserts that a module is enabled.
	 *
	 * @param string $module_id
	 *
	 * @throws ModuleIsDisabled if the module is disabled.
	 */
	protected function assert_module_is_enabled($module_id)
	{
		if (!empty($this->descriptors[$module_id][Descriptor::DISABLED]))
		{
			throw new ModuleIsDisabled($module_id);
		}
	}

	/**
	 * Asserts that a module constructor exists.
	 *
	 * @param string $module_id Module identifier.
	 * @param string $class Constructor class.
	 */
	protected function assert_constructor_exists($module_id, $class)
	{
		if (!class_exists($class, true))
		{
			throw new ModuleConstructorMissing($module_id, $class);
		}
	}

	/**
	 * Instantiate a module.
	 *
	 * @param string $module_id Module identifier.
	 *
	 * @return Module
	 */
	protected function instantiate_module($module_id)
	{
		$this->assert_module_is_defined($module_id);
		$this->assert_module_is_enabled($module_id);

		$descriptor = $this->descriptors[$module_id];
		$class = $descriptor[Descriptor::CLASSNAME];

		$this->assert_constructor_exists($module_id, $class);

		$parent = &$descriptor[Descriptor::INHERITS];

		if ($parent)
		{
			$parent = $this[$parent];
		}

		return new $class($this, $descriptor);
	}

	/**
	 * Defines module constants.
	 *
	 * @param array $descriptors
	 */
	protected function define_constants(array $descriptors)
	{
		foreach ($descriptors as $descriptor)
		{
			$namespace = $descriptor[Descriptor::NS];
			$constant = $namespace . '\DIR';

			if (!defined($constant))
			{
				define($constant, $descriptor[Descriptor::PATH]);
			}
		}
	}
}
