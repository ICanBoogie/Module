<?php

namespace ICanBoogie\Module;

use ICanBoogie\ActiveRecord\ConnectionCollection;
use ICanBoogie\ActiveRecord\Model;
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

				Model::SCHEMA => []

			]

		];

		/* @var $connections ConnectionCollection */
		/* @var $modules ModuleCollection */

		$collection = new ModelCollection($connections, $modules, $definitions);

		$this->assertTrue($collection->offsetExists($model_id));
	}
}
