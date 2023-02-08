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

use ICanBoogie\Routing\RouteDefinition;

/**
 * Extends its parent class with options for module routes.
 */
class ModuleRouteDefinition extends RouteDefinition
{
    /**
     * Module identifier
     */
    public const MODULE = 'module';
}
