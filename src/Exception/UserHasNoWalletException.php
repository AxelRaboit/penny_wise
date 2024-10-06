<?php

namespace App\Exception;

use RuntimeException;
use Throwable;

class UserHasNoWalletException extends RuntimeException
{
    public function __construct(string $message = 'User has no wallet.', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}