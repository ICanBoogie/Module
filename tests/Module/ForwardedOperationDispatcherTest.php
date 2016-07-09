<?php

namespace ICanBoogie\Module;

use ICanBoogie\EventCollection;
use ICanBoogie\EventCollectionProvider;
use ICanBoogie\HTTP\Request;
use ICanBoogie\HTTP\Status;
use ICanBoogie\Operation;
use ICanBoogie\Operation\Failure;
use ICanBoogie\Operation\Response;

class ForwardedOperationDispatcherTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var ForwardedOperationDispatcher
	 */
	private $dispatcher;

	/**
	 * @var EventCollection
	 */
	private $events;

	/**
	 * @inheritdoc
	 */
	public function setUp()
	{
		$this->dispatcher = new ForwardedOperationDispatcher(\ICanBoogie\app()->modules);
		$this->events = $events = new EventCollection;

		EventCollectionProvider::define(function () use ($events) {

			return $events;

		});
	}

	public function test_invoke_should_return_null_when_request_is_not_an_operation()
	{
		$request = Request::from('/');
		$dispatcher = $this->dispatcher;

		$this->assertSame(null, $dispatcher($request));
	}

	/*
	 * Whatever the outcome of a forwarded operation, the dispatcher must not return a response,
	 * unless the request is an XHR.
	 */

	public function test_forwarded_success()
	{
		$request = Request::from([

			'path' => '/',
			'request_params' => [

				Operation::DESTINATION => 'sample',
				Operation::NAME => 'success'

			]
		]);

		$dispatcher = $this->dispatcher;
		$response = $dispatcher($request);
		$this->assertNull($response);
	}

	public function test_forwarded_success_with_location()
	{
		$request = Request::from([

			'path' => '/',
			'request_params' => [

				Operation::DESTINATION => 'sample',
				Operation::NAME => 'success_with_location'

			]
		]);

		$dispatcher = $this->dispatcher;
		$response = $dispatcher($request);
		$this->assertInstanceOf(Response::class, $response);
		$this->assertNotNull($response->location);
	}

	public function test_forwarded_error()
	{
		$request = Request::from([

			'path' => '/',
			'request_params' => [

				Operation::DESTINATION => 'sample',
				Operation::NAME => 'error'

			]
		]);

		$dispatcher = $this->dispatcher;

		try
		{
			$dispatcher($request);
		}
		catch (Failure $exception)
		{
			$response = $dispatcher->rescue($exception, $request);
			$this->assertNull($response);

			return;
		}

		$this->fail('The Failure exception should have been raised.');
	}

	public function test_forwarded_failure()
	{
		$request = Request::from([

			'path' => '/',
			'request_params' => [

				Operation::DESTINATION => 'sample',
				Operation::NAME => 'failure'

			]
		]);

		$dispatcher = $this->dispatcher;

		try
		{
			$dispatcher($request);
		}
		catch (Operation\Failure $exception)
		{
			$response = $dispatcher->rescue($exception, $request);
			$this->assertNull($response);

			return;
		}

		$this->fail('The Failure exception should have been raised.');
	}

	/*
	 * The response to a forwarded operation must be return if the request is an XHR.
	 */

	public function test_forwarded_success_with_xhr()
	{
		$request = Request::from([

			'path' => '/',
			'is_xhr' => true,
			'request_params' => [

				Operation::DESTINATION => 'sample',
				Operation::NAME => 'success'

			]
		]);

		$dispatcher = $this->dispatcher;
		$response = $dispatcher($request);

		$this->assertInstanceOf(Response::class, $response);
		$this->assertEquals(Status::OK, $response->status->code);
		$this->assertTrue($response->status->is_successful);
	}

	public function test_forwarded_success_with_xhr_and_location()
	{
		$request = Request::from([

			'path' => '/',
			'is_xhr' => true,
			'request_params' => [

				Operation::DESTINATION => 'sample',
				Operation::NAME => 'success_with_location'

			]
		]);

		$dispatcher = $this->dispatcher;
		$response = $dispatcher($request);

		$this->assertInstanceOf(Response::class, $response);
		$this->assertEquals(Status::OK, $response->status->code);
		$this->assertTrue($response->status->is_successful);
		$this->assertNull($response->location);
		$this->assertNotNull($response['redirect_to']);
	}

	public function test_forwarded_error_with_xhr()
	{
		$request = Request::from([

			'path' => '/',
			'is_xhr' => true,
			'request_params' => [

				Operation::DESTINATION => 'sample',
				Operation::NAME => 'error'

			]
		]);

		$dispatcher = $this->dispatcher;

		try
		{
			$dispatcher($request);
		}
		catch (Failure $exception)
		{
			$response = $dispatcher->rescue($exception, $request);
			$this->assertInstanceOf(Response::class, $response);

			return;
		}

		$this->fail('The Failure exception should have been raised.');
	}

	public function test_forwarded_failure_with_xhr()
	{
		$request = Request::from([

			'path' => '/',
			'is_xhr' => true,
			'request_params' => [

				Operation::DESTINATION => 'sample',
				Operation::NAME => 'failure'

			]
		]);

		$dispatcher = $this->dispatcher;

		try
		{
			$dispatcher($request);
		}
		catch (Failure $exception)
		{
			$response = $dispatcher->rescue($exception, $request);
			$this->assertInstanceOf(Response::class, $response);

			return;
		}

		$this->fail('The Failure exception should have been raised.');
	}

	public function test_rescue_just_throw_exceptions_which_are_not_failures()
	{
		$exception = new \Exception;
		$request = Request::from();

		try
		{
			$this->dispatcher->rescue($exception, $request);
		}
		catch (\Exception $e)
		{
			$this->assertSame($exception, $e);
		}
	}

	public function test_rescue_should_return_recovered_response()
	{
		$request = Request::from();
		$response = new Response;
		$operation_response = new Response(null, 500);

		/* @var $operation Operation */
		$operation = $this->getMockBuilder(Operation::class)
			->disableOriginalConstructor()
			->getMockForAbstractClass();
		$operation->response = $operation_response;

		$exception = new \Exception;
		$failure = new Failure($operation, $exception);

		$this->events->attach(function (Operation\RescueEvent $event, Operation $target) use ($operation, $failure, $response) {

			$this->assertSame($operation, $target);
			$this->assertSame($failure, $event->exception);

			$event->response = $response;

		});

		$this->assertSame($response, $this->dispatcher->rescue($failure, $request));
	}
}
