<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module\Operation;

use ICanBoogie\ActiveRecord;
use ICanBoogie\ActiveRecord\SchemaColumn;
use ICanBoogie\ErrorCollection;
use ICanBoogie\Module;
use ICanBoogie\Operation;

/**
 * The "save" operation is used to create or update a record.
 *
 * @property array $properties The properties to save.
 */
class SaveOperation extends Operation
{
	/**
	 * Change controls:
	 *
	 * - CONTROL_PERMISSION: Module::PERMISSION_CREATE
	 * - CONTROL_OWNERSHIP: true
	 * - CONTROL_FORM: true
	 *
	 * @return array
	 */
	protected function get_controls()
	{
		return [

			self::CONTROL_PERMISSION => Module::PERMISSION_CREATE,
			self::CONTROL_RECORD => true,
			self::CONTROL_OWNERSHIP => true,
			self::CONTROL_FORM => true

		] + parent::get_controls();
	}

	/**
	 * Filters out the operation's parameters, which are not defined as fields by the
	 * primary model of the module, and take care of filtering or resolving properties values.
	 *
	 * Fields defined as 'boolean'
	 * ---------------------------
	 *
	 * The value of the property is filtered using the filter_var() function and the
	 * FILTER_VALIDATE_BOOLEAN filter. If the property in the operation params is empty, the
	 * property value is set the `false`.
	 *
	 * Fields defined as 'varchar'
	 * ---------------------------
	 *
	 * If the property is not empty in the operation params, the property value is trimmed using the
	 * trim() function, ensuring that there is no leading or trailing white spaces.
	 *
	 * **Note::** The getter should only be called during the {@link process()} method.
	 *
	 * @return array The properties of the operation.
	 */
	protected function lazy_get_properties()
	{
		$schema = $this->module->model->extended_schema;
		$request = $this->request;
		$properties = array_intersect_key($request->params, $schema->columns);

		foreach ($schema as $identifier => $column)
		{
			$type = $column->type;

			if ($type == SchemaColumn::TYPE_BOOLEAN)
			{
				if ($column->null && ($request[$identifier] === null || $request[$identifier] === ''))
				{
					$properties[$identifier] = null;
				}
				else
				{
					if (empty($properties[$identifier]))
					{
						$properties[$identifier] = false;

						continue;
					}

					$properties[$identifier] = filter_var($properties[$identifier], FILTER_VALIDATE_BOOLEAN);
				}
			}
			else if ($type == SchemaColumn::TYPE_VARCHAR)
			{
				if (empty($properties[$identifier]) || !is_string($properties[$identifier]))
				{
					continue;
				}

				$properties[$identifier] = trim($properties[$identifier]);
			}
		}

		unset($properties[$schema->primary]);

		return $properties;
	}

	/**
	 * Overrides the getter to prevent exceptions when the operation key is empty.
	 */
	protected function lazy_get_record()
	{
		return $this->key ? parent::lazy_get_record() : null;
	}

	/**
	 * Overrides the method in order for the control to pass if the operation key is empty, which
	 * is the case when creating a new record.
	 */
	protected function control_record()
	{
		return $this->key ? parent::control_record() : true;
	}

	/**
	 * The method simply returns true.
	 *
	 * @param ErrorCollection $errors
	 *
	 * @return bool
	 */
	protected function validate(ErrorCollection $errors)
	{
		return true;
	}

	/**
	 * Creates or updates a record in the module's primary model.
	 *
	 * A record is created if the operation's key is empty, otherwise an existing record is
	 * updated.
	 *
	 * The method uses the `properties` property to get the properties used to create or update
	 * the record.
	 *
	 * @return array An array composed of the save mode ('update' or 'new') and the record's
	 * key.
	 *
	 * @throws \RuntimeException when saving the record fails.
	 */
	protected function process()
	{
		$key = $this->key;
		$properties = $this->properties;
		$log_params = [ 'key' => $key, 'module' => $this->module->title ];

		try
		{
			$record_key = $key
				? $this->update_record($properties)
				: $this->create_record($properties, $this->record);
		}
		catch (ActiveRecord\RecordNotValid $e)
		{
			$this->response->errors->merge($e->errors->to_error_collection());

			throw $e;
		}

		if (!$record_key)
		{
			throw new \RuntimeException($this->format($key ? 'Unable to update record %key in %module.' : 'Unable to create record in %module.', $log_params));
		}

		$this->response->location = $this->request->uri;
		$this->response->message = $this->format($key ? 'The record %key in %module has been saved.' : 'A new record has been saved in %module.', $log_params);

		return [ 'mode' => $key ? 'update' : 'new', 'key' => $record_key ];
	}

	/**
	 * Update the operation record with properties.
	 *
	 * @param array $properties
	 *
	 * @return bool|int
	 */
	protected function update_record(array $properties)
	{
		return $this->record->assign($properties)->save();
	}

	/**
	 * Creates a record from properties.
	 *
	 * @param array $properties
	 * @param ActiveRecord $record The new record is saved in that variable.
	 *
	 * @return bool|int
	 */
	protected function create_record(array $properties, &$record)
	{
		$record = $this->module->model->new($properties);

		return $record->save();
	}
}
