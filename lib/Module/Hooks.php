<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module;

use ICanBoogie\ActiveRecord;
use ICanBoogie\AppConfig;
use ICanBoogie\Binding\Routing\BeforeSynthesizeRoutesEvent;
use ICanBoogie\Application;
use ICanBoogie\Facets\Fetcher;
use ICanBoogie\Facets\Fetcher\BasicFetcher;
use ICanBoogie\Facets\RecordCollection;
use ICanBoogie\HTTP\RequestDispatcher;
use ICanBoogie\Module;
use ICanBoogie\Operation\OperationRouteDispatcher;
use ICanBoogie\PropertyNotDefined;
use ICanBoogie\Render\TemplateResolver;
use ICanBoogie\Routing\Controller;
use ICanBoogie\View\View;

use function ICanBoogie\app;
use function ICanBoogie\format;

/**
 * Hook callbacks.
 */
class Hooks
{
	/*
	 * Events
	 */

	/**
	 * Alter routes defined by modules by adding a `module` key that holds the identifier of the
	 * module that defines the route.
	 *
	 * @param BeforeSynthesizeRoutesEvent $event
	 */
	static public function before_synthesize_routes(BeforeSynthesizeRoutesEvent $event)
	{
		$module_roots = [];

		foreach (self::get_app_modules()->descriptors as $module_id => $descriptor)
		{
			$module_roots[$descriptor[Descriptor::PATH]] = $module_id;
		}

		foreach ($event->fragments as $module_root => &$fragment)
		{
			$module_root = dirname(dirname($module_root)) . DIRECTORY_SEPARATOR;

			if (empty($module_roots[$module_root]))
			{
				continue;
			}

			$module_id = $module_roots[$module_root];

			foreach ($fragment as $route_id => &$route)
			{
				$route += [

					ModuleRouteDefinition::MODULE => $module_id

				];
			}
		}
	}

	/**
	 * Decorates the template resolver with a {@link ModuleTemplateResolver} instance.
	 *
	 * @param TemplateResolver\AlterEvent $event
	 */
	static public function on_template_resolver_alter(TemplateResolver\AlterEvent $event)
	{
		$event->instance = new ModuleTemplateResolver($event->instance, self::get_app_modules());
	}

	/**
	 * If the view renders a module's route, the "template" directory of that module is added
	 * to the list of templates locations. Also, the module is set as `module` view variable.
	 *
	 * @param View\AlterEvent $event
	 * @param View $target
	 */
	static public function on_view_alter(View\AlterEvent $event, View $target)
	{
		try
		{
			/* @var $controller ControllerBindings */
			$controller = $target->controller;
			$module = $controller->module;
		}
		catch (PropertyNotDefined $e)
		{
			return;
		}

		$target['module'] = $module;
	}

	/**
	 * @param RequestDispatcher\AlterEvent $event
	 * @param RequestDispatcher $target
	 */
	static public function on_alter_request_dispatcher(RequestDispatcher\AlterEvent $event, RequestDispatcher $target)
	{
		$routing = $target['routing'];

		self::assert_routing_dispatcher_is_valid($routing);

		$modules = self::get_app_modules();

		$target['routing'] = new ModuleOperationDispatcher($routing->routes, $modules);
		$event->insert_before('forwarded_operation', new ForwardedOperationDispatcher($modules), 'routing');
	}

	/**
	 * Clears modules cache.
	 *
	 * @param Application\ClearCacheEvent $event
	 * @param Application $app
	 */
	static public function on_app_clear_cache(Application\ClearCacheEvent $event, Application $app)
	{
		$vars = $app->vars;
		$iterator = new \RegexIterator($vars->getIterator(), '/^cached_modules_/');

		foreach ($iterator as $key)
		{
			$vars->eliminate($key);
		}
	}

	/*
	 * Prototypes
	 */

	/**
	 * Return the {@link ModuleCollection} instance used to manage the modules attached to the _core_.
	 *
	 * @param Application $app
	 *
	 * @return ModuleCollection The modules provider.
	 */
	static public function get_modules(Application $app)
	{
		$config = $app->config;

		return new ModuleCollection(
			$config[ModuleAutoconfig::MODULES],
			$config[AppConfig::CACHE_MODULES] ? $app->vars : null
		);
	}

	/**
	 * Returns the {@link ModelCollection} instance used to obtain the models defined by the modules.
	 *
	 * @param Application $app
	 *
	 * @return ModelCollection The models accessor.
	 */
	static public function get_models(Application $app)
	{
		return new ModelCollection($app->connections, $app->modules);
	}

	/**
	 * Return the {@link Module} instance associated with the route handled by the controller.
	 *
	 * @param Controller|ControllerBindings $controller
	 *
	 * @return Module
	 */
	static public function controller_get_module(Controller $controller)
	{
		return $controller->app->modules[$controller->route->module];
	}

	/**
	 * Return the primary model of the module associated with the route handled by the controller.
	 *
	 * @param Controller|ControllerBindings $controller
	 *
	 * @return \ICanBoogie\ActiveRecord\Model
	 *
	 * @see controller_get_module()
	 */
	static public function controller_get_model(Controller $controller)
	{
		return $controller->module->model;
	}

	/**
	 * Return a record fetcher for the controller `model`.
	 *
	 * **Note:** The "icanboogie/facets" package is required.
	 *
	 * @param Controller|ControllerBindings $controller
	 *
	 * @return BasicFetcher
	 */
	static public function controller_lazy_get_records_fetcher(Controller $controller)
	{
		return new BasicFetcher($controller->model);
	}

	/**
	 * Fetch records using the controller `records_fetcher`.
	 *
	 * @param Controller|ControllerBindings $controller
	 * @param array $modifiers
	 *
	 * @return RecordCollection
	 */
	static public function controller_fetch_records(Controller $controller, array $modifiers)
	{
		$fetcher = $controller->records_fetcher;

		return $fetcher($modifiers);
	}

	/**
	 * Fetch records using the controller `records_fetcher`.
	 *
	 * @param Controller|ControllerBindings $controller
	 * @param array $modifiers
	 * @param Fetcher|null $fetcher Reference to a variable where the fetcher should be stored.
	 *
	 * @return ActiveRecord
	 */
	static public function controller_fetch_record(Controller $controller, array $modifiers, &$fetcher = null)
	{
		$fetcher = $controller->records_fetcher;
		$records = $fetcher($modifiers);

		if (!$records) {
			return null;
		}

		return $records->one;
	}

	/*
	 * Support
	 */

	/**
	 * Returns the application's module collection.
	 *
	 * @return ModuleCollection
	 */
	static private function get_app_modules()
	{
		static $modules;

		return $modules
			?: $modules = app()->modules;
	}

	/**
	 * Asserts that a dispatcher is an instance of {@link \ICanBoogie\Routing\RouteDispacther}.
	 *
	 * @param mixed $dispatcher
	 *
	 * @throws \LogicException if the dispatcher is not an instance of
	 * {@link \ICanBoogie\Routing\RouteDispacther}.
	 */
	static private function assert_routing_dispatcher_is_valid($dispatcher)
	{
		if (!$dispatcher instanceof OperationRouteDispatcher)
		{
			throw new \LogicException(format("Expected `routing` dispatcher to be an instance of %expected, got %actual instead.", [

				'expected' => OperationRouteDispatcher::class,
				'actual' => get_class($dispatcher)

			]));
		}
	}
}
