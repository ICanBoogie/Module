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
 * @method RecordCollection fetch_records(array $modifiers)
 *     Fetches records using the controller `records_fetcher`.
 *     @see \ICanBoogie\Module\Hooks::controller_fetch_records
 *
 * @method ActiveRecord fetch_record(array $modifiers, &$fetcher = null)
 *     Fetches a record using the controller `records_fetcher`.
 *     @see \ICanBoogie\Module\Hooks::controller_fetch_record
 *
 * @property-read Core $app
 * @property-read Module $module
 * @property-read Model $model
 * @property-read ModuleRoute $route
 * @property Fetcher $records_fetcher
 */
trait ControllerBindings
{

}
