<?php

namespace ICanBoogie\Module;

use ICanBoogie\Autoconfig\Autoconfig;
use ICanBoogie\Config;
use ICanBoogie\Application;
use ICanBoogie\EventCollection;
use ICanBoogie\Module;
use ICanBoogie\Render\Renderer;
use ICanBoogie\Routing\Controller;
use ICanBoogie\Routing\Route;
use ICanBoogie\View\View;

class HooksTest extends \PHPUnit_Framework_TestCase
{
	public function test_on_core_boot()
	{
		$prototype_config = [];

		$event_config = [

			uniqid()

		];

		$configs = $this
			->getMockBuilder(Config::class)
			->disableOriginalConstructor()
			->setMethods([ 'offsetGet' ])
			->getMock();
		$configs
			->expects($this->exactly(2))
			->method('offsetGet')
			->willReturnCallback(function($offset) use ($event_config, $prototype_config) {

				if ($offset === 'event')
				{
					return $event_config;
				}
				else if ($offset === 'prototype')
				{
					return $prototype_config;
				}

			});

		$events = $this
			->getMockBuilder(EventCollection::class)
			->disableOriginalConstructor()
			->setMethods([ 'attach_many' ])
			->getMock();
		$events
			->expects($this->once())
			->method('attach_many')
			->with($event_config);

		$app = $this
			->getMockBuilder(Application::class)
			->disableOriginalConstructor()
			->setMethods([ 'lazy_get_configs', 'lazy_get_events' ])
			->getMock();
		$app
			->expects($this->once())
			->method('lazy_get_configs')
			->willReturn($configs);
		$app
			->expects($this->once())
			->method('lazy_get_events')
			->willReturn($events);

		$event = $this
			->getMockBuilder(Application\BootEvent::class)
			->disableOriginalConstructor()
			->getMock();

		/* @var $event Application\BootEvent */
		/* @var $app Application */

		Hooks::on_app_boot($event, $app);
	}

	public function test_on_alter_view_no_module()
	{
		$route = $this
			->getMockBuilder(Route::class)
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$controller = $this
			->getMockBuilder(Controller::class)
			->disableOriginalConstructor()
			->setMethods([ 'get_route' ])
			->getMockForAbstractClass();
		$controller
			->expects($this->once())
			->method('get_route')
			->willReturn($route);

		$event = $this
			->getMockBuilder(View\AlterEvent::class)
			->disableOriginalConstructor()
			->getMock();

		$view = $this
			->getMockBuilder(View::class)
			->setConstructorArgs([ $controller, \ICanBoogie\Render\get_renderer() ])
			->setMethods([ 'offsetSet' ])
			->getMock();
		$view
			->expects($this->never())
			->method('offsetSet');

		/* @var $controller Controller */
		/* @var $event View\AlterEvent */
		/* @var $view View */

		Hooks::on_view_alter($event, $view);
	}

	public function test_on_alter_view()
	{
		$module = $this
			->getMockBuilder(Module::class)
			->disableOriginalConstructor()
			->getMock();

		$controller = $this
			->getMockBuilder(Controller::class)
			->disableOriginalConstructor()
			->setMethods([ 'get_module' ])
			->getMockForAbstractClass();
		$controller
			->expects($this->once())
			->method('get_module')
			->willReturn($module);

		$event = $this
			->getMockBuilder(View\AlterEvent::class)
			->disableOriginalConstructor()
			->getMock();

		$renderer = $this
			->getMockBuilder(Renderer::class)
			->disableOriginalConstructor()
			->getMock();

		$view = $this
			->getMockBuilder(View::class)
			->setConstructorArgs([ $controller, $renderer ])
			->setMethods(null)
			->getMock();

		/* @var $event View\AlterEvent */
		/* @var $view View */

		Hooks::on_view_alter($event, $view);

		$this->assertArrayHasKey('module', $view);
		$this->assertSame($module, $view['module']);
	}
}
