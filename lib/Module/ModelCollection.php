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
	public function __construct(
		ConnectionCollection $connections,
		private readonly ModuleCollection $modules,
		array $definitions = []
	) {
		parent::__construct($connections, $definitions);
	}

	/**
	 * Checks if a model exists by first checking if the module it belongs to is enabled and that
	 * it actually defines the model.
	 *
	 * @param string $id
	 */
	public function offsetExists(mixed $id): bool
	{
		[ $module_id, $model_id ] = explode('/', $id) + [ 1 => 'primary' ];

		if (empty($this->modules[$module_id]))
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
	public function offsetGet(mixed $id): ActiveRecord\Model
	{
		if (parent::offsetExists($id))
		{
			return parent::offsetGet($id);
		}

		[ $module_id, $model_id ] = explode('/', $id) + [ 1 => 'primary' ];

		return $this->modules[$module_id]->model($model_id);
	}
}
