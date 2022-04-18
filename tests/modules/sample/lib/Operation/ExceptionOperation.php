<?php

namespace Test\ICanBoogie\Module\ModulesTest\Sample\Operation;

use ICanBoogie\ErrorCollection;
use ICanBoogie\Operation;
use ICanBoogie\Module\ModulesTest\Sample\SampleException;

class ExceptionOperation extends Operation
{
	protected function validate(ErrorCollection $errors)
	{
		throw new SampleException('My Exception Message.', 500);
	}

	protected function process()
	{
		return true;
	}
}
