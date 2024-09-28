<?php

namespace App\Validator\TransactionTag;

use App\Entity\TransactionTag;
use App\Entity\User;
use App\Repository\TransactionTagRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class MaxTagsLimitValidator extends ConstraintValidator
{
    private TransactionTagRepository $transactionTagRepository;

    public function __construct(TransactionTagRepository $transactionTagRepository)
    {
        $this->transactionTagRepository = $transactionTagRepository;
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof MaxTransactionTags || !$value instanceof TransactionTag || $value->getId() !== null) {
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
