<?php

namespace App\Service\Voter\Account\Wallet\Transaction;

use App\Entity\Transaction;
use App\Exception\TransactionAccessDeniedException;
use App\Security\Voter\Transaction\TransactionVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class TransactionVoterService
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker
    ) {}

    public function canAccessTransaction(Transaction $transaction): void
    {
        if (!$this->authorizationChecker->isGranted(TransactionVoter::ACCESS_TRANSACTION, $transaction)) {
            throw new TransactionAccessDeniedException();
        }
    }
}