<?php

namespace ICanBoogie\Module;

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
			->getMockBuilder('ICanBoogie\Core\BootEvent')
			->disableOriginalConstructor()
			->getMock();

		$config = $this
			->getMockBuilder('ICanBoogie\Config')
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
			->getMockBuilder('ICanBoogie\Module\ModuleCollection')
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
			->getMockBuilder('ICanBoogie\Event\Events')
			->disableOriginalConstructor()
			->setMethods([ 'configure' ])
			->getMock();
		$events
			->expects($this->once())
			->method('configure')
			->with($events_config);

		$app = $this
			->getMockBuilder('ICanBoogie\Core')
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

		/* @var $event \ICanBoogie\Core\BootEvent */
		/* @var $app \ICanBoogie\Core */

		Hooks::on_core_boot($event, $app);
	}

	public function test_on_alter_view_no_module()
	{
		$controller = $this
			->getMockBuilder('ICanBoogie\Routing\Controller')
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$event = $this
			->getMockBuilder('ICanBoogie\View\View\AlterEvent')
			->disableOriginalConstructor()
			->getMock();

		$view = $this
			->getMockBuilder('ICanBoogie\View\View')
			->setConstructorArgs([ $controller ])
			->setMethods([ 'add_path' ])
			->getMock();
		$view
			->expects($this->never())
			->method('add_path');

		/* @var $event \ICanBoogie\View\View\AlterEvent */
		/* @var $view \ICanBoogie\View\View */

		Hooks::on_view_alter($event, $view);
	}

	public function test_on_alter_view()
	{
		$module_path = uniqid();
		$module_descriptor = [

			Descriptor::PATH => $module_path

		];

		$module = $this
			->getMockBuilder('ICanBoogie\Module')
			->disableOriginalConstructor()
			->setMethods([ 'get_descriptor' ])
			->getMock();
		$module
			->expects($this->once())
			->method('get_descriptor')
			->willReturn($module_descriptor);

		$controller = $this
			->getMockBuilder('ICanBoogie\Routing\Controller')
			->disableOriginalConstructor()
			->setMethods([ 'lazy_get_module' ])
			->getMockForAbstractClass();
		$controller
			->expects($this->once())
			->method('lazy_get_module')
			->willReturn($module);

		$event = $this
			->getMockBuilder('ICanBoogie\View\View\AlterEvent')
			->disableOriginalConstructor()
			->getMock();

		$view = $this
			->getMockBuilder('ICanBoogie\View\View')
			->setConstructorArgs([ $controller ])
			->setMethods([ 'add_path' ])
			->getMock();
		$view
			->expects($this->once())
			->method('add_path')
			->with($module_path . 'templates');

		/* @var $event \ICanBoogie\View\View\AlterEvent */
		/* @var $view \ICanBoogie\View\View */

		Hooks::on_view_alter($event, $view);
	}
}
