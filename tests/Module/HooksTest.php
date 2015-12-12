<?php

namespace ICanBoogie\Module;

use ICanBoogie\Config;
use ICanBoogie\Core;
use ICanBoogie\EventCollection;
use ICanBoogie\Module;
use ICanBoogie\Render\BasicTemplateResolver;
use ICanBoogie\Routing\Controller;
use ICanBoogie\Routing\Route;
use ICanBoogie\View\View;

class HooksTest extends \PHPUnit_Framework_TestCase
{
	public function test_filter_autoconfig()
	{
		$autoconfig = [ 'app-paths' => [ \ICanBoogie\DOCUMENT_ROOT ] ];
		Hooks::filter_autoconfig($autoconfig);
		$this->assertArrayHasKey('module-path', $autoconfig);
		$this->assertEquals($autoconfig['module-path'], [ \ICanBoogie\DOCUMENT_ROOT . 'modules' ]);
	}

	public function test_on_core_configure()
	{
		$modules_config_paths = [

			uniqid(),
			uniqid(),
			uniqid()

		];

		$module_locale_paths = [

			uniqid(),
			uniqid(),
			uniqid()

		];

		$modules = $this
			->getMockBuilder(ModuleCollection::class)
			->disableOriginalConstructor()
			->setMethods([ 'lazy_get_index', 'lazy_get_config_paths', 'get_locale_paths' ])
			->getMock();
		$modules
			->expects($this->once())
			->method('lazy_get_index')
			->willReturn([]);
		$modules
			->expects($this->once())
			->method('lazy_get_config_paths')
			->willReturn($modules_config_paths);
		$modules
			->expects($this->once())
			->method('get_locale_paths')
			->willReturn($module_locale_paths);

		$config = $this
			->getMockBuilder(Config::class)
			->disableOriginalConstructor()
			->setMethods([ 'offsetGet', 'offsetSet' ])
			->getMock();
		$config
			->expects($this->once())
			->method('offsetGet')
			->with('locale-path')
			->willReturn([]);
		$config
			->expects($this->once())
			->method('offsetSet')
			->with('locale-path', $module_locale_paths);

		$configs = $this
			->getMockBuilder(Config::class)
			->disableOriginalConstructor()
			->setMethods([ 'add' ])
			->getMock();
		$configs
			->expects($this->once())
			->method('add')
			->with($modules_config_paths, \ICanBoogie\Autoconfig\Config::CONFIG_WEIGHT_MODULE);

		$app = $this
			->getMockBuilder(Core::class)
			->disableOriginalConstructor()
			->setMethods([ 'lazy_get_config', 'lazy_get_configs', 'lazy_get_modules' ])
			->getMock();
		$app
			->expects($this->once())
			->method('lazy_get_config')
			->willReturn($config);
		$app
			->expects($this->once())
			->method('lazy_get_configs')
			->willReturn($configs);
		$app
			->expects($this->once())
			->method('lazy_get_modules')
			->willReturn($modules);

		$event = $this
			->getMockBuilder(Core\ConfigureEvent::class)
			->disableOriginalConstructor()
			->getMock();

		/* @var $event Core\ConfigureEvent */
		/* @var $app Core */

		Hooks::on_core_configure($event, $app);
	}

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
			->getMockBuilder(Core::class)
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
			->getMockBuilder(Core\BootEvent::class)
			->disableOriginalConstructor()
			->getMock();

		/* @var $event Core\BootEvent */
		/* @var $app Core */

		Hooks::on_core_boot($event, $app);
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
			->setConstructorArgs([ $controller ])
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

		$template_resolver = $this
			->getMockBuilder(BasicTemplateResolver::class)
			->disableOriginalConstructor()
			->setMethods([ 'add_path' ])
			->getMock();
		$template_resolver
			->expects($this->once())
			->method('add_path')
			->with($module_path . 'templates');

		$view = $this
			->getMockBuilder(View::class)
			->setConstructorArgs([ $controller ])
			->setMethods([ 'get_template_resolver' ])
			->getMock();
		$view
			->expects($this->once())
			->method('get_template_resolver')
			->willReturn($template_resolver);

		/* @var $event View\AlterEvent */
		/* @var $view View */

		Hooks::on_view_alter($event, $view);
	}
}
