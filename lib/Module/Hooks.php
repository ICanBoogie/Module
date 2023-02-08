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

use ICanBoogie\Application;
use ICanBoogie\Binding\Routing\BeforeSynthesizeRoutesEvent;
use ICanBoogie\HTTP\RequestDispatcher;
use ICanBoogie\Operation\OperationRouteDispatcher;
use ICanBoogie\Routing\Route;

use LogicException;

use RegexIterator;

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
     * @var array<string, string>
     *     Where _key_ is a route id (or action) and _value_ is a module identifier.
     *
     * @internal
     *
     * @deprecated
     */
    static public array $route_module_mapping;

    /**
     * Alter routes defined by modules by adding a `module` key that holds the identifier of the
     * module that defines the route.
     *
     * @param BeforeSynthesizeRoutesEvent $event
     *
     * @deprecated
     */
    static public function before_synthesize_routes(BeforeSynthesizeRoutesEvent $event): void
    {
        $module_roots = [];

        foreach (self::get_app_modules()->descriptors as $module_id => $descriptor) {
            $module_roots[$descriptor[Descriptor::PATH]] = $module_id;
        }

        foreach ($event->fragments as $module_root => &$fragment) {
            $module_root = dirname(dirname($module_root)) . DIRECTORY_SEPARATOR;

            if (empty($module_roots[$module_root])) {
                continue;
            }

            $module_id = $module_roots[$module_root];
            $route_module_mapping = [];

            foreach ($fragment as $route) {
                assert($route instanceof Route);

                $route_id = $route->id ?? $route->action;

                $route_module_mapping[$route_id] = $module_id;
            }
        }

        self::$route_module_mapping = $route_module_mapping;
    }

    /**
     * @param RequestDispatcher\AlterEvent $event
     * @param RequestDispatcher $target
     */
    static public function on_alter_request_dispatcher(
        RequestDispatcher\AlterEvent $event,
        RequestDispatcher $target
    ): void {
        $event->chain(function () use ($event, $target) {
            $routing = $target['routing'];

            self::assert_routing_dispatcher_is_valid($routing);

            $modules = self::get_app_modules();

            $target['routing'] = new ModuleOperationDispatcher($routing->routes, $modules);
            $event->insert_before('forwarded_operation', new ForwardedOperationDispatcher($modules), 'routing');
        });
    }

    /**
     * Clears modules cache.
     *
     * @param Application\ClearCacheEvent $event
     * @param Application $app
     */
    static public function on_app_clear_cache(Application\ClearCacheEvent $event, Application $app): void
    {
        $vars = $app->vars;
        $iterator = new RegexIterator($vars->getIterator(), '/^cached_modules_/');

        foreach ($iterator as $key) {
            $vars->eliminate($key);
        }
    }

    /*
     * Support
     */

    /**
     * Returns the application's module collection.
     */
    static private function get_app_modules(): ModuleCollection
    {
        static $modules;

        return $modules ??= app()->modules;
    }

    /**
     * Asserts that a dispatcher is an instance of {@link \ICanBoogie\Routing\RouteDispacther}.
     *
     * @param mixed $dispatcher
     *
     * @throws LogicException if the dispatcher is not an instance of
     * {@link \ICanBoogie\Routing\RouteDispacther}.
     */
    static private function assert_routing_dispatcher_is_valid($dispatcher): void
    {
        if (!$dispatcher instanceof OperationRouteDispatcher) {
            throw new LogicException(
                format("Expected `routing` dispatcher to be an instance of %expected, got %actual instead.", [

                    'expected' => OperationRouteDispatcher::class,
                    'actual' => get_class($dispatcher)

                ])
            );
        }
    }
}
