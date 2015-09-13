<?php

namespace ICanBoogie\Module\ModulesTest\Sample\Operation;

use ICanBoogie\Errors;
use ICanBoogie\Operation;

class FailureOperation extends Operation
{
	protected function validate(Errors $errors)
	{
		return true;
	}

	protected function process()
	{
		return;
	}
}
