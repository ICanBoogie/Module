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

use ICanBoogie\Errors;
use ICanBoogie\Module;
use ICanBoogie\Operation;

/**
 * Deletes a record.
 */
class DeleteOperation extends Operation
{
	/**
	 * Modifies the following controls:
	 *
	 * - PERMISSION: MANAGE
	 * - RECORD: true
	 * - OWNERSHIP: true
	 */
	protected function get_controls()
	{
		return [

			self::CONTROL_PERMISSION => Module::PERMISSION_MANAGE,
			self::CONTROL_RECORD => true,
			self::CONTROL_OWNERSHIP => true

		] + parent::get_controls();
	}

	/**
	 * @inheritdoc
	 */
	protected function validate(Errors $errors)
	{
		return true;
	}

	/**
	 * Delete the target record.
	 */
	protected function process()
	{
		$key = $this->key;
		$record = $this->record;

		if (!$record->delete())
		{
			throw new \RuntimeException($this->format('Unable to delete the record %key from %module.', [

				'key' => $key,
				'module' => $this->module->title

			]));
		}

		if ($this->request['redirect_to'])
		{
			$this->response->location = $this->request['redirect_to'];
		}

		$this->response->message = $this->format('The record %key has been deleted from %module.', [

			'key' => $key,
			'module' => $this->module->title

		]);

		return $key;
	}
}
