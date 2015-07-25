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
use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Core;
use ICanBoogie\Facets\Fetcher;
use ICanBoogie\Facets\RecordCollection;
use ICanBoogie\Module;

/**
 * {@link \ICanBoogie\Routing\Controller} prototype bindings.
 *
 * @property-read Core $app
 * @property-read Module $module
 * @property-read Model $model
 * @property-read ModuleRoute $route
 * @property Fetcher $records_fetcher
 */
trait ControllerBindings
{
	/**
	 * @see \ICanBoogie\Module\Hooks::controller_get_module
	 *
	 * @return Module
	 */
	protected function get_module()
	{
		return parent::get_module();
	}

	/**
	 * @see \ICanBoogie\Module\Hooks::controller_get_model
	 *
	 * @return Model
	 */
	protected function get_model()
	{
		return parent::get_model();
	}

	/**
	 * @see \ICanBoogie\Module\Hooks::controller_lazy_get_records_fetcher
	 *
	 * @return Fetcher
	 */
	protected function lazy_get_records_fetcher()
	{
		return parent::lazy_get_records_fetcher();
	}

	/**
	 * Fetch records using the controller `records_fetcher`.
	 *
	 * @see \ICanBoogie\Module\Hooks::controller_fetch_records
	 *
	 * @param array $modifiers
	 *
	 * @return RecordCollection
	 */
	protected function fetch_records(array $modifiers)
	{
		return parent::fetch_records($modifiers);
	}

	/**
	 * Fetch records using the controller `records_fetcher`.
	 *
	 * @see \ICanBoogie\Module\Hooks::controller_fetch_record
	 *
	 * @param array $modifiers
	 * @param Fetcher|null $fetcher Reference to a variable where the fetcher should be stored.
	 *
	 * @return ActiveRecord
	 */
	protected function fetch_record(array $modifiers, &$fetcher = null)
	{
		return parent::fetch_record($modifiers, $fetcher);
	}
}
