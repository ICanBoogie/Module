<?php

namespace ICanBoogie\Module;

use ICanBoogie\Module;
use ICanBoogie\Render\Renderer;
use ICanBoogie\Routing\Controller;
use ICanBoogie\Routing\Route;
use ICanBoogie\View\View;

class HooksTest extends \PHPUnit_Framework_TestCase
{
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
