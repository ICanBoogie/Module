<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* @var $autoload \Composer\Autoload\ClassLoader */

$autoload = require __DIR__ . '/../vendor/autoload.php';
$autoload->setPsr4('ICanBoogie\Module\ModulesTest\ModuleA\\', __DIR__ . '/modules/a/lib');
$autoload->setPsr4('ICanBoogie\Module\ModulesTest\ModuleB\\', __DIR__ . '/modules/b/lib');
$autoload->setPsr4('ICanBoogie\Module\ModulesTest\ModuleC\\', __DIR__ . '/modules/c/lib');
