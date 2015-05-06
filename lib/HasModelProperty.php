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

use ICanBoogie\ActiveRecord\Model;

/**
 * Indicates that an instance has a `model` property.
 *
 * **Note:** The "icanboogie/activerecord" package is required.
 *
 * @property-read Model $model
 */
interface HasModelProperty
{

}
