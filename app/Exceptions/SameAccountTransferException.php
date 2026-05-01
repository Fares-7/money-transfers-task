<?php

namespace App\Exceptions;

use Exception;

class SameAccountTransferException extends Exception
{
    public function __construct($message = "Cannot transfer money to the same account.", $code = 422, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
