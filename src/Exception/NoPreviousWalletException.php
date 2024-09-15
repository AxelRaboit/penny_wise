<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NoPreviousWalletException extends NotFoundHttpException
{
    public function __construct()
    {
        parent::__construct('No previous wallet found');
    }
}
