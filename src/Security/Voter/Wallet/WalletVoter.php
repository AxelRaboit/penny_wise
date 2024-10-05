<?php

declare(strict_types=1);

namespace App\Security\Voter\Wallet;

use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\Wallet\WalletRepository;
use Override;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Wallet|null>
 */
class WalletVoter extends Voter
{
    public const string ACCESS_WALLET = 'ACCESS_WALLET';

    public function __construct(private readonly WalletRepository $walletRepository) {}

    #[Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::ACCESS_WALLET === $attribute && $subject instanceof Wallet;
    }

    #[Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Wallet|null $wallet */
        $wallet = $subject;

        return match ($attribute) {
            self::ACCESS_WALLET => $wallet instanceof Wallet && $this->canAccessWallet($user, $wallet),
            default => false,
        };
    }

    private function canAccessWallet(User $user, Wallet $wallet): bool
    {
        /** @var int $walletId */
        $walletId = $wallet->getId();

        $userWallet = $this->walletRepository->findSpecificWalletByUser($user, $walletId);

        return $userWallet instanceof Wallet;
    }
}
