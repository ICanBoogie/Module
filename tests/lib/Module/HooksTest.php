<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\ICanBoogie\Module;

use ICanBoogie\Module;
use ICanBoogie\Render\Renderer;
use ICanBoogie\Routing\ControllerAbstract;
use ICanBoogie\Routing\Route;
use ICanBoogie\View\View;
use PHPUnit\Framework\TestCase;

final class HooksTest extends TestCase
{
	public function test_on_alter_view_no_module(): void
	{
		$this->markTestSkipped();

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

		$event = new View\AlterEvent($view);

		Hooks::on_view_alter($event, $view);
	}

	public function test_on_alter_view()
	{
		$this->markTestSkipped();

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

		$event = new View\AlterEvent($view);

		Hooks::on_view_alter($event, $view);

		$this->assertArrayHasKey('module', $view);
		$this->assertSame($module, $view['module']);
	}
}
