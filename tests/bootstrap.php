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

use ICanBoogie\Autoconfig\Config;

$_SERVER['DOCUMENT_ROOT'] = __DIR__;

/* @var $autoload \Composer\Autoload\ClassLoader */

$autoload = require __DIR__ . '/../vendor/autoload.php';
$autoload->setPsr4('ICanBoogie\Module\ModulesTest\ModuleA\\', __DIR__ . '/modules/a/lib');
$autoload->setPsr4('ICanBoogie\Module\ModulesTest\ModuleB\\', __DIR__ . '/modules/b/lib');
$autoload->setPsr4('ICanBoogie\Module\ModulesTest\ModuleC\\', __DIR__ . '/modules/c/lib');
$autoload->setPsr4('ICanBoogie\Module\ModulesTest\Sample\\', __DIR__ . '/modules/sample/lib');

(new Core(array_merge_recursive(get_autoconfig(), [

	'config-path' => [

		__DIR__ . '/../config' => Config::CONFIG_WEIGHT_APP

	],

	'module-path' => [

		__DIR__ . DIRECTORY_SEPARATOR . 'modules'

	]

])))->boot();
