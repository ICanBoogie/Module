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

use ICanBoogie\Module\ModuleCollection;
use ICanBoogie\Module\ModuleTemplateResolver;
use PHPUnit\Framework\TestCase;

use function ICanBoogie\app;

final class ServicesTest extends TestCase
{
	/**
	 * @dataProvider provide_service
	 */
	public function test_service(string $id, string $class): void
	{
		$this->assertInstanceOf($class, app()->service_for_id($id, $class));
	}

	public function provide_service(): array
	{
		return [

			[ 'test.modules', ModuleCollection::class ],
			[ 'test.template_resolver', ModuleTemplateResolver::class ],

		];
	}
}
