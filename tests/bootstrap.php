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

use ICanBoogie\Autoconfig\Autoconfig;

chdir(__DIR__);

$_SERVER['DOCUMENT_ROOT'] = __DIR__;

/* @var $autoload \Composer\Autoload\ClassLoader */

$autoload = require __DIR__ . '/../vendor/autoload.php';
$autoload->setPsr4('ICanBoogie\Module\ModulesTest\ModuleA\\', __DIR__ . '/modules/a/lib');
$autoload->setPsr4('ICanBoogie\Module\ModulesTest\ModuleB\\', __DIR__ . '/modules/b/lib');
$autoload->setPsr4('ICanBoogie\Module\ModulesTest\ModuleC\\', __DIR__ . '/modules/c/lib');
$autoload->setPsr4('ICanBoogie\Module\ModulesTest\Sample\\', __DIR__ . '/modules/sample/lib');

class Application extends Core
{
	use Module\ApplicationBindings;
	use Binding\ActiveRecord\ApplicationBindings;
}

boot(array_merge_recursive(get_autoconfig(), [

	Autoconfig::CONFIG_PATH => [

		__DIR__ . '/../config' => Autoconfig::CONFIG_WEIGHT_APP,
		__DIR__ . '/config' => Autoconfig::CONFIG_WEIGHT_APP,

	],

	Autoconfig::MODULE_PATH => [

		__DIR__ . DIRECTORY_SEPARATOR . 'modules'

	]

]));
