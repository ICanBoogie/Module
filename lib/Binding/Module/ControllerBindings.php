<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Binding\Module;

use ICanBoogie\ActiveRecord;
use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Application;
use ICanBoogie\Facets\Fetcher\BasicFetcher;
use ICanBoogie\Facets\RecordCollection;
use ICanBoogie\Module;
use ICanBoogie\Module\ModuleRoute;
use ICanBoogie\Routing\Route;

/**
 * {@link \ICanBoogie\Routing\ControllerAbstract} prototype bindings.
 *
 * @method RecordCollection fetch_records(array $modifiers)
 *     Fetches records using the controller `records_fetcher`.
 *     @see \ICanBoogie\Module\Hooks::controller_fetch_records
 *
 * @method ActiveRecord fetch_record(array $modifiers, &$fetcher = null)
 *     Fetches a record using the controller `records_fetcher`.
 *     @see \ICanBoogie\Module\Hooks::controller_fetch_record
 *
 * @property-read Application $app
 * @property-read Module $module
 * @property-read Model $model
 * @property-read Route|ModuleRoute $route
 * @property BasicFetcher $records_fetcher
 */
trait ControllerBindings
{

}
