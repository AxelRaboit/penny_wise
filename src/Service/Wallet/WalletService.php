<?php

declare(strict_types=1);

namespace App\Service\Wallet;

use App\Entity\User;
use App\Exception\UserHasNoWalletException;
use App\Repository\Wallet\WalletRepository;

final readonly class WalletService
{
    public function __construct(private WalletRepository $walletRepository) {}

    /**
     * Ensure the user has at least one wallet.
     *
     * @throws UserHasNoWalletException if the user has no wallet
     */
    public function ensureUserHasWallet(User $user): void
    {
        if (!$this->walletRepository->userHasWallet($user)) {
            throw new UserHasNoWalletException('User does not have a wallet. Please create one.');
        }
    }
}
