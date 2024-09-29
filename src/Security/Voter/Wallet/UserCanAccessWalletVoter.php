<?php

namespace App\Security\Voter\Wallet;

use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\WalletRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserCanAccessWalletVoter extends Voter
{
    public const string ACCESS_WALLET = 'ACCESS_WALLET';

    public function __construct(private readonly WalletRepository $walletRepository) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::ACCESS_WALLET && $subject instanceof Wallet;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Wallet $wallet */
        $wallet = $subject;
        if (!$wallet instanceof Wallet) {
            return false;
        }

        /** @var int $walletId */
        $walletId = $wallet->getId();

        $wallet = $this->walletRepository->findSpecificWalletByUser($user, $walletId);
        if (null === $wallet) {
            return false;
        }

        return true;
    }
}
