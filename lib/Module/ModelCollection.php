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

use ICanBoogie\ActiveRecord;
use ICanBoogie\ActiveRecord\ConnectionCollection;

/**
 * Model collection.
 *
 * Extends the ActiveRecord model collection with the models defined by the modules.
 */
class ModelCollection extends ActiveRecord\ModelCollection
{
	/**
	 * @var ModuleCollection
	 */
	private $modules;

	/**
	 * @param ConnectionCollection $connections Connections manager.
	 * @param ModuleCollection $modules ModuleCollection manager.
	 * @param array $definitions Model definitions.
	 */
	public function __construct(ConnectionCollection $connections, ModuleCollection $modules, array $definitions = [])
	{
		parent::__construct($connections, $definitions);

		$this->modules = $modules;
	}

	/**
	 * Checks if a model exists by first checking if the module it belongs to is enabled and that
	 * it actually defines the model.
	 *
	 * @param string $id
	 *
	 * @return bool
	 */
	public function offsetExists($id)
	{
		list($module_id, $model_id) = explode('/', $id) + [ 1 => 'primary' ];

		if (!isset($this->modules[$module_id]))
		{
			return parent::offsetExists($id);
		}

		$descriptor = $this->modules->descriptors[$module_id];

		return isset($descriptor[Descriptor::MODELS][$model_id]);
	}

	/**
	 * Gets the specified model of the specified module.
	 *
	 * The pattern used to request a model is `<module_id>[/<model_id>]` where `<module_id>` is
	 * the identifier of the module and `<model_id>` is the identifier of the module's model. The
	 * `<model_id>` part is optional and defaults to `primary`.
	 *
	 * @param string $id Identifier of the model.
	 *
	 * @return ActiveRecord\Model
	 */
	public function offsetGet($id)
	{
		if (isset($this->instances[$id]))
		{
			return $this->instances[$id];
		}

		list($module_id, $model_id) = explode('/', $id) + [ 1 => 'primary' ];

		if (!isset($this->modules[$module_id]))
		{
			return parent::offsetGet($id);
		}

		return $this->instances[$id] = $this->modules[$module_id]->model($model_id);
	}
}
