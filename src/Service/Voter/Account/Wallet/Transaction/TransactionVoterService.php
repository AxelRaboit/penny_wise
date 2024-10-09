<?php

declare(strict_types=1);

namespace App\Service\Voter\Account\Wallet\Transaction;

use App\Entity\Transaction;
use App\Security\Voter\Transaction\TransactionVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class TransactionVoterService
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker
    ) {}

    public function canAccessTransaction(Transaction $transaction): bool
    {
        return $this->authorizationChecker->isGranted(TransactionVoter::ACCESS_TRANSACTION, $transaction);
    }
}
