<?php

declare(strict_types=1);

namespace App\Validator\TransactionTag;

use App\Entity\TransactionTag;
use App\Entity\User;
use App\Repository\TransactionTagRepository;
use Override;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TransactionTagMaxTagsLimitValidator extends ConstraintValidator
{
    public function __construct(private readonly TransactionTagRepository $transactionTagRepository) {}

    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof TransactionTagMaxTransactionTags || !$value instanceof TransactionTag || null !== $value->getId()) {
            return;
        }

        /** @var User $user */
        $user = $value->getUser();

        $tagCount = $this->transactionTagRepository->findByUserCount($user);
        if ($tagCount >= $constraint->limit) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ limit }}', (string) $constraint->limit)
                ->addViolation();
        }
    }
}
