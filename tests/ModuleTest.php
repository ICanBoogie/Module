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

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\ActiveRecord\Connection;
use ICanBoogie\Module\Descriptor;

class ModuleTest extends \PHPUnit_Framework_TestCase
{
	static private $connection;
	static private $node_descriptor;
	static private $node_module;
	static private $content_module;

	static public function setupBeforeClass()
	{
		self::$connection = new Connection('sqlite::memory:');

		self::$node_descriptor = [

			Descriptor::ID => 'nodes',
			Descriptor::MODELS => [

				'primary' => [

					Model::ACTIVERECORD_CLASS => __CLASS__ . '\Modules\Nodes\Node',
					Model::CONNECTION => self::$connection,
					Model::SCHEMA => [

						'fields' => [

							'nid' => 'serial',
							'title' => [ 'varchar', 80 ]

						]
					]
				]
			],

			Descriptor::NS => __CLASS__ . '\Modules\Nodes',
			Descriptor::TITLE => 'Nodes'
		];

		self::$node_module = new Module(self::$node_descriptor);

		self::$content_module = new Module
		([
			Descriptor::ID => 'contents',
			Descriptor::INHERITS => self::$node_module,
			Descriptor::MODELS => [

				'primary' => [

					Model::EXTENDING => 'nodes',
					Model::SCHEMA => [

						'date' => 'datetime'
					]
				]
			]
		]);
	}

	/**
	 * @dataProvider provide_test_write_readonly_property
	 * @expectedException \ICanBoogie\PropertyNotWritable
	 */
	public function test_write_readonly_property($property)
	{
		self::$node_module->$property = null;
	}

	public function provide_test_write_readonly_property()
	{
		$properties = 'descriptor|flat_id|id|model|parent|path|title';

		return array_map(function($v) { return (array) $v; }, explode('|', $properties));
	}

	public function test_get_descriptor()
	{
		$this->assertEquals(self::$node_descriptor, self::$node_module->descriptor);
	}

	public function test_get_flat_id()
	{
		$m = new Module([

			Descriptor::ID => 'name.space.to.id',
			Descriptor::TITLE => 'Nodes'
		]);

		$this->assertEquals('name_space_to_id', $m->flat_id);
	}

	public function test_get_id()
	{
		$this->assertEquals(self::$node_descriptor[Descriptor::ID], self::$node_module->id);
	}

	public function test_get_model()
	{
		$this->assertInstanceOf('ICanBoogie\ActiveRecord\Model', self::$node_module->model);
	}

	public function test_get_parent()
	{
		$this->assertEquals(self::$node_module, self::$content_module->parent);
	}
}
