<?php

namespace Test\ICanBoogie\Module\ModulesTest\Sample\Operation;

use ICanBoogie\ErrorCollection;
use ICanBoogie\Operation;
use ICanBoogie\Operation\FormHasExpired;

class ExpiredOperation extends Operation
{
    protected function validate(ErrorCollection $errors)
    {
        throw new FormHasExpired();
    }

    protected function process()
    {
        return true;
    }
}
