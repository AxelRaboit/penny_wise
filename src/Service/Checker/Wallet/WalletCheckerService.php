<?php

declare(strict_types=1);

namespace App\Service\Checker\Wallet;

use App\Entity\Wallet;
use App\Repository\Wallet\WalletRepository;
use LogicException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class WalletCheckerService
{
    public function __construct(private WalletRepository $walletRepository) {}

    /**
     * Retrieve a wallet by its account, year, and month.
     */
    public function getWalletOrThrow(int $accountId, int $year, int $month): Wallet
    {
        $wallet = $this->walletRepository->findWalletByAccountYearAndMonth($accountId, $year, $month);
        if (!$wallet instanceof Wallet) {
            throw new NotFoundHttpException('Wallet not found for the given year and month');
        }

        return $wallet;
    }

    /**
     * Returns the wallet or null if not found.
     */
    public function getWalletOrNull(int $accountId, int $year, int $month): ?Wallet
    {
        return $this->walletRepository->findWalletByAccountYearAndMonth($accountId, $year, $month);
    }

    /**
     * Returns the wallet or throws an exception if not found.
     */
    public function getWalletByIdOrThrow(int $id): Wallet
    {
        $wallet = $this->walletRepository->find($id);
        if (!$wallet instanceof Wallet) {
            throw new NotFoundHttpException('Wallet not found for the given ID');
        }

        return $wallet;
    }

    /**
     * Returns the wallet or null if not found.
     */
    public function getWalletByIdOrNull(int $id): ?Wallet
    {
        return $this->walletRepository->find($id);
    }

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
