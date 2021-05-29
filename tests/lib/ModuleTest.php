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
use ICanBoogie\Module\ModuleCollection;
use PHPUnit\Framework\TestCase;

final class ModuleTest extends TestCase
{
	static private $connection;
	static private $node_descriptor;
	private $node_module;
	private $content_module;

	static public function setupBeforeClass(): void
	{
		self::$connection = new Connection('sqlite::memory:');

		self::$node_descriptor = [

			Descriptor::ID => 'nodes',
			Descriptor::MODELS => [

				'primary' => [

					Model::CONNECTION => self::$connection,
					Model::SCHEMA => [

						'nid' => 'serial',
						'title' => [ 'varchar', 80 ]

					]
				]
			],

			Descriptor::NS => __CLASS__ . '\Modules\Nodes',
			Descriptor::TITLE => 'Nodes'
		];
	}

	protected function setUp(): void
	{
		/* @var $collection_stub ModuleCollection */
		$collection_stub = $this
			->getMockBuilder(ModuleCollection::class)
			->disableOriginalConstructor()
			->getMock();

		$this->node_module = new Module($collection_stub, self::$node_descriptor);

		$this->content_module = new Module
		(
			$collection_stub, [
			Descriptor::ID => 'contents',
			Descriptor::INHERITS => $this->node_module,
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
	 */
	public function test_write_readonly_property(string $property): void
	{
		$this->expectException(PropertyNotWritable::class);
		$this->node_module->$property = null;
	}

	public function provide_test_write_readonly_property(): array
	{
		$properties = 'descriptor|flat_id|id|model|parent|path|title';

		return array_map(function($v) { return (array) $v; }, explode('|', $properties));
	}

	public function test_get_descriptor()
	{
		$this->assertEquals(self::$node_descriptor, $this->node_module->descriptor);
	}

	public function test_get_flat_id()
	{
		/* @var $collection_stub ModuleCollection */
		$collection_stub = $this
			->getMockBuilder(ModuleCollection::class)
			->disableOriginalConstructor()
			->getMock();

		$m = new Module($collection_stub, [

			Descriptor::ID => 'name.space.to.id',
			Descriptor::TITLE => 'Nodes'

		]);

		$this->assertEquals('name_space_to_id', $m->flat_id);
	}

	public function test_get_id()
	{
		$this->assertEquals(self::$node_descriptor[Descriptor::ID], $this->node_module->id);
	}

	public function test_get_model()
	{
		$this->assertInstanceOf(Model::class, $this->node_module->model);
	}

	public function test_get_parent()
	{
		$this->assertEquals($this->node_module, $this->content_module->parent);
	}
}
