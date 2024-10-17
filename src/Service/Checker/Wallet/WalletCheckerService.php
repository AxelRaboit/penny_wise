<?php

declare(strict_types=1);

namespace App\Service\Checker\Wallet;

use App\Entity\Wallet;
use App\Repository\Wallet\WalletRepository;
use LogicException;

final readonly class WalletCheckerService
{
    public function __construct(private WalletRepository $walletRepository) {}

    /**
     * Check if the given wallet exists.
     */
    public function ensureWalletDoesNotExist(int $accountId, int $year, int $month): void
    {
        $existingWallet = $this->walletRepository->findWalletByAccountYearAndMonth($accountId, $year, $month);
        if ($existingWallet instanceof Wallet) {
            throw new LogicException('A wallet for the same year and month already exists.');
        }
    }
}
