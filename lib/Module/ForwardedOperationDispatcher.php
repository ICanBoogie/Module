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

use ICanBoogie\HTTP\Dispatcher;
use ICanBoogie\HTTP\Request;
use ICanBoogie\Module;
use ICanBoogie\Operation;

use function ICanBoogie\camelize;
use function ICanBoogie\log_error;
use function ICanBoogie\format;

/**
 * A dispatcher for forwarded operations.
 */
class ForwardedOperationDispatcher implements Dispatcher
{
	/**
	 * Formats the specified namespace and operation name into an operation class.
	 *
	 * @param string $operation_name
	 *
	 * @return string
	 */
	static private function format_class_name($operation_name)
	{
		return camelize(strtr($operation_name, '-', '_')) . 'Operation';
	}

	/**
	 * @var ModuleCollection
	 */
	private $modules;

	/**
	 * @param ModuleCollection $modules
	 */
	public function __construct(ModuleCollection $modules)
	{
		$this->modules = $modules;
	}

	/**
	 * @inheritdoc
	 *
	 * @return Operation\Response
	 */
	public function __invoke(Request $request)
	{
		if (!$request[Operation::DESTINATION] && !$request[Operation::NAME])
		{
			return null;
		}

		$this->assert_forward_is_valid($request);

		$operation = $this->resolve_operation($request);
		$response = $operation($request);

		return $request->is_xhr || $response->location ? $response : null;
	}

	/**
	 * If the exception in not a {@link Failure} instance it is rethrown, otherwise it means
	 * that an exception occurred during control/validate/process or a Failure was thrown
	 * because the response has an error.
	 *
	 * - If the request is an XHR we return the response of the operation.
	 *
	 * - Otherwise, the exception message is logged as an error and no response is returned
	 * so that the actual content of the URL is displayed.
	 *
	 * @inheritdoc
	 */
	public function rescue(\Exception $exception, Request $request)
	{
		if (!$exception instanceof Operation\Failure)
		{
			throw $exception;
		}

		if ($request->is_xhr)
		{
			return $exception->operation->response;
		}

		$response = null;

		new Operation\RescueEvent($exception->operation, $exception, $request, $response);

		if ($response)
		{
			return $response;
		}

		$previous = $exception->previous;

		if ($previous)
		{
			log_error($previous->getMessage());
		}

		return null;
	}

	/**
	 * Asserts that a forward is valid.
	 *
	 * @param Request $request
	 *
	 * @throws \LogicException if {@link Operation::DESTINATION} or {@link Operation::NAME} is
	 * not defined.
	 */
	protected function assert_forward_is_valid(Request $request)
	{
		if (!$request[Operation::DESTINATION])
		{
			throw new \LogicException("The operation's destination is required.");
		}

		if (!$request[Operation::NAME])
		{
			throw new \LogicException("The operation's name is required.");
		}
	}

	/**
	 * Resolves an {@link Operation} instance from forwarded parameters.
	 *
	 * @param Request $request
	 *
	 * @return Operation
	 */
	protected function resolve_operation(Request $request)
	{
		$module_id = $request[Operation::DESTINATION];
		$module = $this->modules[$module_id];
		$operation_name = $request[Operation::NAME];
		$operation_key = $request[Operation::KEY];
		$constructor = $this->resolve_operation_constructor($operation_name, $module);

		return $constructor([

			'key' => $operation_key,
			'module' => $module

		]);
	}

	/**
	 * Resolves operation constructor.
	 *
	 * @param string $operation_name
	 * @param Module $module
	 *
	 * @return callable
	 *
	 * @throws \LogicException If the {@link Operation} instance cannot be resolved.
	 */
	protected function resolve_operation_constructor($operation_name, Module $module)
	{
		$operation_class = $this->modules->resolve_classname('Operation\\' . self::format_class_name($operation_name), $module);

		if (!$operation_class)
		{
			throw new \LogicException(format
			(
				'The operation %operation is not supported by the module %module.', [

					'%module' => $module->id,
					'%operation' => $operation_name
				]
			), 404);
		}

		return [ $operation_class, 'from' ];
	}
}
