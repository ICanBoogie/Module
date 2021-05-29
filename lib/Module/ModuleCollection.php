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

use ArrayIterator;
use BadMethodCallException;
use ICanBoogie\Accessor\AccessorTrait;
use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\ErrorCollection;
use ICanBoogie\Module;
use ICanBoogie\Storage\Storage;
use ICanBoogie\Module\ModuleCollection\InstallableFilter;

use LogicException;

use function ICanBoogie\format;
use function ICanBoogie\camelize;
use function ICanBoogie\singularize;
use function ICanBoogie\stable_sort;

/**
 * A module collection.
 *
 * @property-read array $descriptors Modules descriptors.
 */
class ModuleCollection implements \ArrayAccess, \IteratorAggregate
{
	/**
	 * @uses get_descriptors
	 */
	use AccessorTrait;

	/**
	 * Formats a SQL table name given the module id and the model id.
	 */
	static public function format_model_name(string $module_id, string $model_id = 'primary'): string
	{
		return preg_replace('#[^0-9a-zA-Z$_]#', '_', $module_id) . ($model_id == 'primary' ? '' : '__' . $model_id);
	}

	/**
	 * The descriptors for the modules.
	 *
	 * @var array
	 */
	private $descriptors;

	protected function get_descriptors(): array
	{
		$this->ensure_modules_are_indexed();

		return $this->descriptors;
	}

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
	 * @param Storage|null $cache The cache to use for the module indexes.
	 */
	public function __construct(array $paths, Storage $cache = null)
	{
		$this->paths = $paths;
		$this->cache = $cache;
	}

	/**
	 * The method is not supported.
	 *
	 * @inheritdoc
	 */
	public function offsetSet($offset, $value)
	{
		throw new BadMethodCallException();
	}

	public function offsetUnset($offset)
	{
		throw new BadMethodCallException();
	}

	/**
	 * Checks if a module exists.
	 *
	 * Note: `empty()` will call {@link offsetGet()} to check if the value is not empty. So, unless
	 * you want to use the module you check, better check using `!isset()`, otherwise the module
	 * you check is loaded too.
	 *
	 * @param string $module_id Module identifier.
	 *
	 * @return bool Whether or not the module is available.
	 */
	public function offsetExists($module_id): bool
	{
		$this->ensure_modules_are_indexed();

		return isset($this->descriptors[$module_id]);
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
	 * @return ArrayIterator<string, Module>
	 */
	public function getIterator(): ArrayIterator
	{
		$this->ensure_modules_are_indexed();

		return new ArrayIterator($this->modules);
	}

	public function filter_descriptors(callable $filter): array
	{
		$this->ensure_modules_are_indexed();

		return array_filter($this->descriptors, $filter);
	}

	public function filter_descriptors_by_users(string $module_id): array
	{
		$this->ensure_modules_are_indexed();

		$users = [];
		$descriptors = $this->descriptors;

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
	 * Obtain index either from cache or by building it.
	 *
	 * @return array|mixed|null
	 */
	private function obtain_descriptors()
	{
		if (!$this->paths)
		{
			return [];
		}

		$cache = $this->cache;
		$action = function () {
			return $this->index_descriptors($this->paths);
		};

		if ($cache)
		{
			$key = 'cached_modules_' . substr(sha1(implode('#', $this->paths)), 0, 8);
			$descriptors = $cache->retrieve($key);

			if ($descriptors)
			{
				return $descriptors;
			}

			$descriptors = $action();
			$cache->store($key, $descriptors);

			return $descriptors;
		}

		return $action();
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
	 * @return array<string, array>
	 */
	private function index_descriptors(array $paths): array
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
			if (empty($descriptors[$id]))
			{
				throw new ModuleNotDefined($id);
			}

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
	 * @param string[] $paths
	 *
	 * @return array<string, array>
	 */
	protected function collect_descriptors(array $paths): array
	{
		$descriptors = [];

		foreach ($paths as $root)
		{
			$root = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			$descriptor_path = $root . 'descriptor.php';

			if (!file_exists($descriptor_path))
			{
				throw new LogicException("Missing `descriptor.php` file in $root.");
			}

			$id = basename(realpath($root));
			$descriptor = $this->read_descriptor($id, $root);
			$descriptors[$descriptor[Descriptor::ID]] = $descriptor;
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
	 * @return array A module descriptor
	 */
	protected function read_descriptor(string $module_id, string $path): array
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
				'%name is required. Invalid descriptor in %path.', [

					'name' => Descriptor::TITLE,
					'path' => $descriptor_path

				]
			));
		}

		if (empty($descriptor[Descriptor::NS]))
		{
			throw new \InvalidArgumentException(format
			(
				'%name is required. Invalid descriptor in %path.', [

					'name' => Descriptor::NS,
					'path' => $descriptor_path

				]
			));
		}

		return Descriptor::normalize($descriptor + [

			Descriptor::ID => $module_id,
			Descriptor::PATH => $path,

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
	protected function alter_descriptor(array $descriptor): array
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
	 * Orders the module ids provided according to module inheritance and weight.
	 *
	 * @param string[] $ids The module ids to order.
	 * @param array<string, array>|null $descriptors Module descriptors.
	 *
	 * @return array
	 */
	public function order_ids(array $ids, array $descriptors = null): array
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
	 *
	 * @return int
	 */
	public function usage(string $module_id): int
	{
		return count($this->filter_descriptors_by_users($module_id));
	}

	/**
	 * Checks if a module inherits from another.
	 *
	 * @param string $module_id Module identifier.
	 * @param string $parent_id Identifier of the parent module.
	 *
	 * @return boolean `true` if the module inherits from the other.
	 */
	public function is_inheriting(string $module_id, string $parent_id): bool
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
	public function install(ErrorCollection $errors = null): ErrorCollection
	{
		if (!$errors)
		{
			$errors = new ErrorCollection;
		}

		foreach (array_keys($this->filter_descriptors(new InstallableFilter($this))) as $module_id)
		{
			try
			{
				$this[$module_id]->install($errors);
			}
			catch (\Throwable $e)
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
	public function resolve_classname(string $unqualified_classname, string $module_id, array &$tried = [])
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
	 * Ensures that modules are indexed, index them if not.
	 *
	 * The method obtains modules descriptors and defined associated constants.
	 */
	protected function ensure_modules_are_indexed(): void
	{
		$descriptors = &$this->descriptors;

		if ($descriptors !== null)
		{
			return;
		}

		$descriptors = $this->obtain_descriptors();
		$this->define_constants($descriptors);
	}

	/**
	 * Asserts that a module is defined.
	 *
	 * @param string $module_id Module identifier.
	 *
	 * @throws ModuleNotDefined if the module is not defined.
	 */
	protected function assert_module_is_defined(string $module_id): void
	{
		if (empty($this->descriptors[$module_id]))
		{
			throw new ModuleNotDefined($module_id);
		}
	}

	protected function assert_constructor_exists(string $module_id, string $class): void
	{
		if (!class_exists($class, true))
		{
			throw new ModuleConstructorMissing($module_id, $class);
		}
	}

	protected function instantiate_module(string $module_id): Module
	{
		$this->assert_module_is_defined($module_id);

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

	protected function define_constants(array $descriptors): void
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
