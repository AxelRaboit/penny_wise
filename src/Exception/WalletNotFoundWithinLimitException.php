<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

class WalletNotFoundWithinLimitException extends Exception
{
    public function __construct(int $limit)
    {
        $message = sprintf('No wallet found within the limit of %d months.', $limit);
        parent::__construct($message);
    }
}
