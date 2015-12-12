<?php

namespace ICanBoogie\Module;

use ICanBoogie\HTTP\Request;
use ICanBoogie\HTTP\Status;
use ICanBoogie\Operation;
use ICanBoogie\Operation\Failure;
use ICanBoogie\Operation\Response;

class ForwardedForwardedOperationDispatcherTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var ForwardedOperationDispatcher
	 */
	private $dispatcher;

	public function setUp()
	{
		$this->dispatcher = new ForwardedOperationDispatcher(\ICanBoogie\app()->modules);
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
}
