<?php

namespace App\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    public function __construct($message = "Insufficient balance for this transfer.", $code = 422, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
