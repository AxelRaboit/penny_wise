<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccountAccessDeniedException extends AccessDeniedException
{
    public function __construct()
    {
        parent::__construct('You do not have permission to access this account.');
    }
}
