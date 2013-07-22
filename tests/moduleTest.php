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

class ModuleTest extends \PHPUnit_Framework_TestCase
{
	static private $connection;
	static private $node_descriptor;
	static private $node_module;
	static private $content_module;

	static public function setupBeforeClass()
	{
		self::$connection = new Connection('sqlite::memory:');

		self::$node_descriptor = array
		(
			Module::T_ID => 'nodes',
			Module::T_MODELS => array
			(
				'primary' => array
				(
					Model::ACTIVERECORD_CLASS => __CLASS__ . '\Modules\Nodes\Node',
					Model::CONNECTION => self::$connection,
					Model::SCHEMA => array
					(
						'fields' => array
						(
							'nid' => 'serial',
							'title' => array('varchar', 80)
						)
					)
				)
			),

			Module::T_NAMESPACE => __CLASS__ . '\Modules\Nodes',
			Module::T_TITLE => 'Nodes'
		);

		self::$node_module = new Module(self::$node_descriptor);

		self::$content_module = new Module
		(
			array
			(
				Module::T_ID => 'contents',
				Module::T_EXTENDS => self::$node_module,
				Module::T_MODELS => array
				(
					'primary' => array
					(
						Model::EXTENDING => 'nodes',
						Model::SCHEMA => array
						(
							'date' => 'datetime'
						)
					)
				)
			)
		);
	}

	public function test_get_descriptor()
	{
		$this->assertEquals(self::$node_descriptor, self::$node_module->descriptor);
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotWritable
	 */
	public function test_set_descriptor()
	{
		self::$node_module->descriptor = null;
	}

	public function test_get_flat_id()
	{
		$m = new Module(array
		(
			Module::T_ID => 'name.space.to.id',
			Module::T_TITLE => 'Nodes'
		));

		$this->assertEquals('name_space_to_id', $m->flat_id);
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotWritable
	 */
	public function test_set_flat_id()
	{
		$m = new Module(array
		(
			Module::T_ID => 'name.space.to.id',
			Module::T_TITLE => 'Nodes'
		));

		$m->flat_id = null;
	}

	public function test_get_id()
	{
		$this->assertEquals(self::$node_descriptor[Module::T_ID], self::$node_module->id);
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotWritable
	 */
	public function test_set_id()
	{
		self::$node_module->id = null;
	}

	public function test_get_model()
	{
		$this->assertInstanceOf('ICanBoogie\ActiveRecord\Model', self::$node_module->model);
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotWritable
	 */
	public function test_set_model()
	{
		self::$node_module->model = null;
	}

	public function test_get_parent()
	{
		$this->assertEquals(self::$node_module, self::$content_module->parent);
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotWritable
	 */
	public function test_set_parent()
	{
		self::$node_module->parent = null;
	}
}