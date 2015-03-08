<?php

namespace ICanBoogie\Module;

use ICanBoogie\Config;
use ICanBoogie\Core;
use ICanBoogie\Events;
use ICanBoogie\Module;
use ICanBoogie\Routing\Controller;
use ICanBoogie\View\View;

class HooksTest extends \PHPUnit_Framework_TestCase
{
	public function test_filter_autoconfig()
	{
		$autoconfig = [ 'app-paths' => [ __DIR__ . DIRECTORY_SEPARATOR ] ];
		Hooks::filter_autoconfig($autoconfig);
		$this->assertArrayHasKey('module-path', $autoconfig);
		$this->assertEquals($autoconfig['module-path'], [ __DIR__ . DIRECTORY_SEPARATOR . 'modules' ]);
	}

	public function test_on_core_boot()
	{
		$modules_config_paths = [

			uniqid(),
			uniqid(),
			uniqid()

		];

		$events_config = [

			uniqid()

		];

		$event = $this
			->getMockBuilder(Core\BootEvent::class)
			->disableOriginalConstructor()
			->getMock();

		$config = $this
			->getMockBuilder(Config::class)
			->disableOriginalConstructor()
			->setMethods([ 'offsetGet', 'add' ])
			->getMock();
		$config
			->expects($this->exactly(2))
			->method('offsetGet')
			->willReturnCallback(function($id) use ($events_config) {

				switch ($id)
				{
					case 'core': return [

						'cache modules' => false

					];

					case 'prototypes': return [


					];

					case 'events': return $events_config;
				}

				throw new \Exception("Unexpected config request: $id.");

			});
		$config
			->expects($this->once())
			->method('add')
			->with($modules_config_paths, \ICanBoogie\Autoconfig\Config::CONFIG_WEIGHT_MODULE);

		$modules = $this
			->getMockBuilder(ModuleCollection::class)
			->disableOriginalConstructor()
			->setMethods([ 'lazy_get_index', 'lazy_get_config_paths' ])
			->getMock();
		$modules
			->expects($this->once())
			->method('lazy_get_index')
			->willReturn([]);
		$modules
			->expects($this->once())
			->method('lazy_get_config_paths')
			->willReturn($modules_config_paths);

		$events = $this
			->getMockBuilder(Events::class)
			->disableOriginalConstructor()
			->setMethods([ 'configure' ])
			->getMock();
		$events
			->expects($this->once())
			->method('configure')
			->with($events_config);

		$app = $this
			->getMockBuilder(Core::class)
			->disableOriginalConstructor()
			->setMethods([ 'lazy_get_configs', 'lazy_get_modules', 'lazy_get_events' ])
			->getMock();
		$app
			->expects($this->once())
			->method('lazy_get_configs')
			->willReturn($config);
		$app
			->expects($this->once())
			->method('lazy_get_modules')
			->willReturn($modules);
		$app
			->expects($this->once())
			->method('lazy_get_events')
			->willReturn($events);

		/* @var $event Core\BootEvent */
		/* @var $app Core */

		Hooks::on_core_boot($event, $app);
	}

	public function test_on_alter_view_no_module()
	{
		$controller = $this
			->getMockBuilder(Controller::class)
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$event = $this
			->getMockBuilder(View\AlterEvent::class)
			->disableOriginalConstructor()
			->getMock();

		$view = $this
			->getMockBuilder(View::class)
			->setConstructorArgs([ $controller ])
			->setMethods([ 'add_path' ])
			->getMock();
		$view
			->expects($this->never())
			->method('add_path');

		/* @var $event View\AlterEvent */
		/* @var $view View */

		Hooks::on_view_alter($event, $view);
	}

	public function test_on_alter_view()
	{
		$module_path = uniqid();
		$module_descriptor = [

			Descriptor::PATH => $module_path

		];

		$module = $this
			->getMockBuilder(Module::class)
			->disableOriginalConstructor()
			->setMethods([ 'get_descriptor' ])
			->getMock();
		$module
			->expects($this->once())
			->method('get_descriptor')
			->willReturn($module_descriptor);

		$controller = $this
			->getMockBuilder(Controller::class)
			->disableOriginalConstructor()
			->setMethods([ 'lazy_get_module' ])
			->getMockForAbstractClass();
		$controller
			->expects($this->once())
			->method('lazy_get_module')
			->willReturn($module);

		$event = $this
			->getMockBuilder(View\AlterEvent::class)
			->disableOriginalConstructor()
			->getMock();

		$view = $this
			->getMockBuilder(View::class)
			->setConstructorArgs([ $controller ])
			->setMethods([ 'add_path' ])
			->getMock();
		$view
			->expects($this->once())
			->method('add_path')
			->with($module_path . 'templates');

		/* @var $event View\AlterEvent */
		/* @var $view View */

		Hooks::on_view_alter($event, $view);
	}
}
