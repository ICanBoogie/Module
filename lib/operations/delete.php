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

use ICanBoogie\I18n\FormattedString;

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

		if (!$this->module->model->delete($key))
		{
			throw new \RuntimeException(\ICanBoogie\format('Unable to delete the record %key from %module.', [

				'key' => $key,
				'module' => $this->module->title

			]));
		}

		if ($this->request['redirect_to'])
		{
			$this->response->location = $this->request['redirect_to'];
		}

		$this->response->message = new FormattedString('The record %key has been deleted from %module.', [

			'key' => $key,
			'module' => $this->module->title

		]);

		return $key;
	}
}