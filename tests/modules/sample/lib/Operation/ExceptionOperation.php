<?php

namespace ICanBoogie\Module\ModulesTest\Sample\Operation;

use ICanBoogie\Errors;
use ICanBoogie\Operation;
use ICanBoogie\Module\ModulesTest\Sample\SampleException;

class ExceptionOperation extends Operation
{
	protected function validate(Errors $errors)
	{
		throw new SampleException('My Exception Message.', 500);
	}

	protected function process()
	{
		return true;
	}
}
