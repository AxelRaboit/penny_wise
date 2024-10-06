<?php

namespace App\Service\Voter\Account\Wallet;

use App\Entity\Wallet;
use App\Exception\WalletAccessDeniedException;
use App\Security\Voter\Wallet\WalletVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class WalletVoterService
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker
    ) {}

    public function canAccessWallet(Wallet $wallet): void
    {
        if (!$this->authorizationChecker->isGranted(WalletVoter::ACCESS_WALLET, $wallet)) {
            throw new WalletAccessDeniedException();
        }
    }
}