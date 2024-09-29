<?php

declare(strict_types=1);

namespace App\Security\Voter\Wallet;

use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\WalletRepository;
use Override;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Wallet>
 */
class UserCanAccessWalletVoter extends Voter
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

        return $wallet instanceof Wallet;
    }
}
