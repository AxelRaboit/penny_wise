<?php

declare(strict_types=1);

namespace App\Service\Account\Wallet;

use App\Entity\Account;
use App\Entity\Wallet;
use App\Repository\Wallet\WalletRepository;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

final readonly class AccountWalletService
{
    public function __construct(private WalletRepository $walletRepository) {}

    /**
     * Get the next month and year for wallet creation.
     *
     * @param Account $account The account entity
     * @param int     $year    The current year
     *
     * @return array{year: int, month: int} Returns the next available month and year
     */
    public function getNextAvailableMonthAndYear(Account $account, int $year): array
    {
        $accountId = $account->getId();
        if (null === $accountId) {
            throw new NotFoundResourceException('Account ID cannot be null');
        }

        $lastWallet = $this->walletRepository->findLastWalletByAccountAndYear($accountId, $year);

        if ($lastWallet instanceof Wallet) {
            $lastMonth = $lastWallet->getMonth();

            if (12 === $lastMonth) {
                return ['year' => $year + 1, 'month' => 1];
            }

            return ['year' => $year, 'month' => $lastMonth + 1];
        }

        return ['year' => $year, 'month' => 1];
    }
}
