<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NoPreviousBudgetException extends NotFoundHttpException
{
    public function __construct()
    {
        parent::__construct('No previous budget found');
    }
}
