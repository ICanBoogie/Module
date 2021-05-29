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

use ICanBoogie\HTTP\Request;
use ICanBoogie\Module;
use ICanBoogie\Operation;
use ICanBoogie\Routing\Route;
use ICanBoogie\Routing\RouteCollection;

use function ICanBoogie\camelize;

/**
 * A request dispatcher for module operations.
 */
final class ModuleOperationDispatcher extends Operation\OperationRouteDispatcher
{
	/**
	 * @var ModuleCollection
	 */
	private $modules;

	public function __construct(RouteCollection $routes, ModuleCollection $modules)
	{
		$this->modules = $modules;

		parent::__construct($routes);
	}

	/**
	 * @inheritdoc
	 */
	protected function resolve_route(Request $request, $normalized_path, array &$captured)
	{
		return parent::resolve_route($request, $normalized_path, $captured)
			?: $this->resolve_module_route($request, $normalized_path, $captured);
	}

	/**
	 * Resolves module route.
	 *
	 * @return Route|null A made up {@link Route} instance or `null` if the route
	 * cannot be resolved.
	 */
	private function resolve_module_route(Request $request, string $normalized_path, array &$captured): ?Route
	{
		$parsed_path = $this->parse_path($normalized_path);

		if ($parsed_path === false)
		{
			return null;
		}

		[ $module, $operation_name, $operation_key ] = $parsed_path;

		$operation_class = $this->resolve_operation_class($operation_name, $module);

		if (!$operation_class)
		{
			return null;
		}

		$captured[Operation::KEY] = $operation_key;

		$pattern = $operation_key
			? sprintf('/api/:%s/:%s/:%s', Operation::DESTINATION, Operation::KEY, Operation::NAME)
			: sprintf('/api/:%s/:%s', Operation::DESTINATION, Operation::NAME);

		return Route::from([

			ModuleRouteDefinition::PATTERN => $pattern,
			ModuleRouteDefinition::CONTROLLER => $operation_class,
			ModuleRouteDefinition::MODULE => $module->id,
			ModuleRouteDefinition::VIA => $request->method

		]);
	}

	/**
	 * Parse path to extract operation information.
	 *
	 * @return array|bool The operation information or `false` if the path is not suitable.
	 */
	private function parse_path(string $path)
	{
		if (strpos($path, Operation::RESTFUL_BASE) !== 0)
		{
			return false;
		}

		$parts = explode('/', trim($path, '/'));
		array_shift($parts);
		$n = count($parts);

		if ($n < 2 || $n > 4)
		{
			return false;
		}

		$operation_key = null;

		if ($n === 2)
		{
			list($module_id, $operation_name) = $parts;
		}
		else
		{
			list($module_id, $operation_key, $operation_name) = $parts;
		}

		$modules = $this->modules;

		if (!isset($modules[$module_id]))
		{
			return false;
		}

		return [ $modules[$module_id], $operation_name, $operation_key ];
	}

	/**
	 * Resolves the operation class.
	 *
	 * @return false|string
	 */
	private function resolve_operation_class(string $operation_name, Module $module)
	{
		$unqualified_class_name = 'Operation\\' . camelize(strtr($operation_name, '-', '_')) . 'Operation';

		return $this->modules->resolve_classname($unqualified_class_name, $module);
	}
}
