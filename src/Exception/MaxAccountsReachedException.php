<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MaxAccountsReachedException extends AccessDeniedException
{
    public function __construct(string $message = 'You have reached the maximum number of allowed accounts.')
    {
        parent::__construct($message);
    }
}
