<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\ICanBoogie;

use Composer\Autoload\ClassLoader;

use function ICanBoogie\boot;
use function ob_start;

use const DIRECTORY_SEPARATOR;

const SANDBOX = __DIR__ . DIRECTORY_SEPARATOR . 'sandbox' . DIRECTORY_SEPARATOR;

chdir(__DIR__);

/* @var $autoload ClassLoader */

$autoload = require __DIR__ . '/../vendor/autoload.php';
$autoload->setPsr4('ICanBoogie\Module\ModulesTest\ModuleA\\', __DIR__ . '/modules/a/lib');
$autoload->setPsr4('ICanBoogie\Module\ModulesTest\ModuleB\\', __DIR__ . '/modules/b/lib');
$autoload->setPsr4('ICanBoogie\Module\ModulesTest\ModuleC\\', __DIR__ . '/modules/c/lib');
$autoload->setPsr4('ICanBoogie\Module\ModulesTest\Sample\\', __DIR__ . '/modules/sample/lib');

ob_start(); // Prevents PHPUnit from sending headers

boot();
