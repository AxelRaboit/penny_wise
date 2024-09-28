<?php

declare(strict_types=1);

namespace App\Validator\TransactionTag;

use Attribute;
use Override;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class TransactionTagMaxTransactionTags extends Constraint
{
    public string $message = 'You can create a maximum of {{ limit }} tags.';

    public int $limit = 5;

    #[Override]
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

    #[Override]
    public function validatedBy(): string
    {
        return TransactionTagMaxTagsLimitValidator::class;
    }
}
