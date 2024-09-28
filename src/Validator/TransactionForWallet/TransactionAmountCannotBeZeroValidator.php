<?php

declare(strict_types=1);

namespace App\Validator\TransactionForWallet;

use App\Entity\Transaction;
use Override;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TransactionAmountCannotBeZeroValidator extends ConstraintValidator
{
    private const float FORBIDDEN_AMOUNT = 0.0;

    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof TransactionAmountCannotBeZero || !$value instanceof Transaction) {
            return;
        }

        if (self::FORBIDDEN_AMOUNT === $value->getAmount()) {
            $this->context->buildViolation($constraint->message)
                ->atPath('amount')
                ->addViolation();
        }
    }
}
