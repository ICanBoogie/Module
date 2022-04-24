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

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module;
use ICanBoogie\Module\Descriptor;
use ICanBoogie\Module\ModuleCollection;
use ICanBoogie\PropertyNotWritable;
use PHPUnit\Framework\TestCase;

final class ModuleTest extends TestCase
{
	static private array $node_descriptor;
	private Module $node_module;
	private Module $content_module;

	static public function setupBeforeClass(): void
	{
		self::$node_descriptor = [

			Descriptor::ID => 'nodes',
			Descriptor::MODELS => [ 'nodes' ],
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
				Descriptor::MODELS => [ 'contents' ],
			]
		);
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
		$properties = 'flat_id id parent path title';

		return array_map(function ($v) {
			return (array) $v;
		}, explode(' ', $properties));
	}

	public function test_get_descriptor(): void
	{
		$this->assertEquals(self::$node_descriptor, $this->node_module->descriptor);
	}

	public function test_get_flat_id(): void
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

	public function test_get_id(): void
	{
		$this->assertEquals(self::$node_descriptor[Descriptor::ID], $this->node_module->id);
	}

	public function test_get_model(): void
	{
		$this->assertInstanceOf(Model::class, $this->node_module->model);
	}

	public function test_get_parent(): void
	{
		$this->assertEquals($this->node_module, $this->content_module->parent);
	}
}
