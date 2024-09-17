<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\TransactionInformationDto;
use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Exception\NoPreviousTransactionsException;
use App\Exception\NoPreviousWalletException;
use App\Manager\TransactionManager;
use App\Repository\TransactionRepository;
use App\Util\WalletHelper;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

final readonly class TransactionService
{
    public function __construct(private TransactionManager $transactionManager, private EntityManagerInterface $entityManager, private WalletService $walletService, private TransactionRepository $transactionRepository, private WalletHelper $walletHelper) {}

    /**
     * @return TransactionInformationDto Returns a data transfer object with transaction information by user for a given wallet
     */
    public function getAllTransactionInformationByUser(Wallet $wallet): TransactionInformationDto
    {
        return $this->transactionManager->getAllTransactionInformationByUser($wallet);
    }

    /**
     * Copy transactions from the previous month to the current month's wallet.
     *
     * @param Wallet|null $currentWallet         the current wallet to which transactions will be copied
     * @param int         $transactionCategoryId the ID of the transaction category to copy
     */
    public function copyTransactionsFromPreviousMonth(?Wallet $currentWallet, int $transactionCategoryId): void
    {
        if (!$currentWallet instanceof Wallet) {
            throw new InvalidArgumentException();
        }

        $previousMonthData = $this->walletHelper->getPreviousMonthAndYear($currentWallet->getYear(), $currentWallet->getMonth());
        $previousWallet = $this->walletService->getWalletByUser($currentWallet->getIndividual(), $previousMonthData['year'], $previousMonthData['month']);
        if (!$previousWallet instanceof Wallet) {
            throw new NoPreviousWalletException();
        }

        $previousTransactions = $this->transactionRepository->findTransactionsByWalletAndCategory($previousWallet, $transactionCategoryId);
        if ([] === $previousTransactions) {
            throw new NoPreviousTransactionsException();
        }

        foreach ($previousTransactions as $transaction) {
            $newTransaction = new Transaction();
            $newTransaction->setDescription($transaction->getDescription());
            $newTransaction->setAmount($transaction->getAmount());
            $newTransaction->setDate(new DateTimeImmutable());
            $newTransaction->setWallet($currentWallet);
            $newTransaction->setTransactionCategory($transaction->getTransactionCategory());
            $newTransaction->setNature($transaction->getNature());

            $this->entityManager->persist($newTransaction);
        }

        $this->entityManager->flush();
    }

    public function copyLeftToSpendFromPreviousMonth(Wallet $currentWallet): void
    {
        $previousMonthData = $this->walletHelper->getPreviousMonthAndYear($currentWallet->getYear(), $currentWallet->getMonth());
        $previousWallet = $this->walletService->getWalletByUser($currentWallet->getIndividual(), $previousMonthData['year'], $previousMonthData['month']);

        if (!$previousWallet instanceof Wallet) {
            throw new NoPreviousWalletException();
        }

        $transactionInfoDto = $this->getAllTransactionInformationByUser($previousWallet);
        $totalLeftToSpend = $transactionInfoDto->getTotalLeftToSpend();

        $this->transactionManager->copyTransactionsFromPreviousMonth($currentWallet, $totalLeftToSpend);
    }
}
