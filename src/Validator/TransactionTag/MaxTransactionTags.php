<?php

namespace App\Validator\TransactionTag;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class MaxTransactionTags extends Constraint
{
    public string $message = 'You can create a maximum of {{ limit }} tags.';
    public int $limit = 5;

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return MaxTagsLimitValidator::class;
    }
}
