<?php

declare(strict_types=1);

namespace App\Validator\TransactionForWallet;

use Attribute;
use Override;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class TransactionAmountCannotBeZero extends Constraint
{
    public string $message = 'The amount cannot be zero.';

    #[Override]
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

    #[Override]
    public function validatedBy(): string
    {
        return TransactionAmountCannotBeZeroValidator::class;
    }
}
