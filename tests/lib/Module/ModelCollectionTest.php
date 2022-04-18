<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\ICanBoogie\Module;

use ICanBoogie\ActiveRecord\ConnectionCollection;
use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\ActiveRecord\Schema;
use ICanBoogie\Module\ModelCollection;
use ICanBoogie\Module\ModuleCollection;
use PHPUnit\Framework\TestCase;

final class ModelCollectionTest extends TestCase
{
	public function test_should_return_true_if_model_exists_in_model_collection()
	{
		$connections = $this->getMockBuilder(ConnectionCollection::class)
			->disableOriginalConstructor()
			->getMock();

		$modules = $this->getMockBuilder(ModuleCollection::class)
			->disableOriginalConstructor()
			->getMock();

		$model_id = uniqid();

		$definitions = [

			$model_id => [

				Model::SCHEMA => new Schema(),

			]

		];

		/* @var $connections ConnectionCollection */
		/* @var $modules ModuleCollection */

		$collection = new ModelCollection($connections, $modules, $definitions);

		$this->assertTrue($collection->offsetExists($model_id));
	}
}
