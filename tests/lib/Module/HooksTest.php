<?php

namespace ICanBoogie\Module;

use ICanBoogie\Module;
use ICanBoogie\Render\Renderer;
use ICanBoogie\Routing\ControllerAbstract;
use ICanBoogie\Routing\Route;
use ICanBoogie\View\View;
use PHPUnit\Framework\TestCase;

use function ICanBoogie\Render\get_renderer;

final class HooksTest extends TestCase
{
	public function test_on_alter_view_no_module(): void
	{
		$route = new Route('/', 'action');

		$controller = $this
			->getMockBuilder(ControllerAbstract::class)
			->disableOriginalConstructor()
			->onlyMethods([ 'get_route' ])
			->getMockForAbstractClass();
		$controller
			->expects($this->once())
			->method('get_route')
			->willReturn($route);

		$renderer = $this
			->createMock(Renderer::class);

		$view = $this
			->getMockBuilder(View::class)
			->setConstructorArgs([ $controller, $renderer ])
			->onlyMethods([ 'offsetSet' ])
			->getMock();
		$view
			->expects($this->never())
			->method('offsetSet');

		/* @var $event View\AlterEvent */
		$event = View\AlterEvent::from([ 'target' => $view ]);

		Hooks::on_view_alter($event, $view);
	}

	public function test_on_alter_view()
	{
		$module = $this
			->getMockBuilder(Module::class)
			->disableOriginalConstructor()
			->getMock();

		$controller = $this
			->getMockBuilder(ControllerAbstract::class)
			->disableOriginalConstructor()
			->addMethods([ 'get_module' ])
			->getMockForAbstractClass();
		$controller
			->expects($this->once())
			->method('get_module')
			->willReturn($module);

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
		$event = View\AlterEvent::from([ 'target' => $view ]);

		Hooks::on_view_alter($event, $view);

		$this->assertArrayHasKey('module', $view);
		$this->assertSame($module, $view['module']);
	}
}
