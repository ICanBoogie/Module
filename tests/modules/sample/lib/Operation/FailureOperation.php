<?php

namespace Test\ICanBoogie\Module\ModulesTest\Sample\Operation;

use ICanBoogie\ErrorCollection;
use ICanBoogie\Operation;

class FailureOperation extends Operation
{
    protected function validate(ErrorCollection $errors)
    {
        return true;
    }

    protected function process()
    {
        return;
    }
}
