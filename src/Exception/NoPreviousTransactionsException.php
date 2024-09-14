<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NoPreviousTransactionsException extends NotFoundHttpException
{
    public function __construct()
    {
        parent::__construct('No transactions found to copy from the previous month.');
    }
}
