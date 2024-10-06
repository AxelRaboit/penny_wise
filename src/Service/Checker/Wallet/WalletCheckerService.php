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
     * Retrieve a wallet by its account, year, and month or throw an exception if not found.
     *
     * @param int $year  The year of the wallet
     * @param int $month The month of the wallet
     *
     * @return Wallet The retrieved wallet entity
     *
     * @throws NotFoundHttpException If the wallet is not found or if the account ID is null
     */
    /**
     * Retrieve a wallet by its account, year, and month or throw an exception if not found.
     *
     * @param int $accountId The account ID linked to the wallet
     * @param int $year      The year of the wallet
     * @param int $month     The month of the wallet
     *
     * @return Wallet The retrieved wallet entity
     *
     * @throws NotFoundHttpException If the wallet is not found or if the account ID is null
     */
    public function getWalletOrThrow(int $accountId, int $year, int $month): Wallet
    {
        $wallet = $this->walletRepository->findWalletByAccountYearAndMonth($accountId, $year, $month);
        if (!$wallet instanceof Wallet) {
            throw new NotFoundHttpException('Wallet not found for the given year and month');
        }

        return $wallet;
    }

    public function getWalletByIdOrThrow(int $id): Wallet
    {
        $wallet = $this->walletRepository->find($id);
        if (!$wallet instanceof Wallet) {
            throw new NotFoundHttpException('Wallet not found for the given ID');
        }

        return $wallet;
    }

    public function ensureWalletDoesNotExist(int $accountId, int $year, int $month): void
    {
        $existingWallet = $this->walletRepository->findWalletByAccountYearAndMonth($accountId, $year, $month);
        if ($existingWallet instanceof Wallet) {
            throw new LogicException('A wallet for the same year and month is already exists.');
        }
    }
}
