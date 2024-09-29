<?php

namespace App\Security\Voter\Transaction;

use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\TransactionRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserCanAccessTransactionVoter extends Voter
{
    public const string ACCESS_TRANSACTION = 'ACCESS_TRANSACTION';

    public function __construct(private readonly TransactionRepository $transactionRepository) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::ACCESS_TRANSACTION && $subject instanceof Transaction;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Transaction $transaction */
        $transaction = $subject;

        /** @var int $transactionId */
        $transactionId = $transaction->getId();

        $userTransaction = $this->transactionRepository->findSpecificTransactionByUser($user, $transactionId);
        if (!$userTransaction) {
            return false;
        }

        return true;
    }
}
