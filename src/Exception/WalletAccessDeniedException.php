<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Throwable;

class WalletAccessDeniedException extends AccessDeniedException
{
    public function __construct(string $message = 'Access to this wallet is denied.', Throwable $previous = null, int $code = 0)
    {
        parent::__construct($message, $previous, $code);
    }
}