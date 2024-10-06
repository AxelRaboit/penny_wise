<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Throwable;

class TransactionAccessDeniedException extends AccessDeniedException
{
    public function __construct(
        string $message = 'You are not allowed to access this transaction.',
        ?Throwable $previous = null,
        int $code = 0
    ) {
        parent::__construct($message, $previous, $code);
    }
}
