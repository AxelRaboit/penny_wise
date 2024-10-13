<?php

declare(strict_types=1);

namespace App\Controller\Account\Wallet\Transaction;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Form\Transaction\TransactionForWalletType;
use App\Manager\Refacto\Account\Wallet\Transaction\WalletTransactionCreationManager;
use App\Manager\Refacto\Account\Wallet\Transaction\WalletTransactionDeleteManager;
use App\Manager\Refacto\Account\Wallet\Transaction\WalletTransactionManager;
use App\Service\EntityAccessService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TransactionWalletController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WalletTransactionManager $walletTransactionManager,
        private readonly WalletTransactionDeleteManager $walletTransactionDeleteManager,
        private readonly WalletTransactionCreationManager $walletTransactionCreationManager,
        private readonly EntityAccessService $entityAccessService,
    ) {}

    #[Route('/account/{accountId}/wallet/{walletId}/transaction/new', name: 'new_transaction_wallet')]
    public function newForWallet(int $accountId, int $walletId, Request $request): Response
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        $wallet = $this->entityAccessService->getWalletWithAccessCheck($walletId);
        if (!$wallet instanceof Wallet) {
            return $this->redirectToRoute('account_list');
        }

        $user = $wallet->getIndividual();

        $transaction = $this->walletTransactionCreationManager->beginTransactionCreationWithWallet($wallet, $user);

        $form = $this->createForm(TransactionForWalletType::class, $transaction, [
            'wallet' => $wallet,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->walletTransactionCreationManager->saveTransactionWallet($transaction);

            return $this->redirectToWalletDashboard($wallet);
        }

        return $this->render('account/wallet/dashboard/transaction/new.html.twig', [
            'form' => $form,
            'wallet' => $wallet,
            'transaction' => $transaction,
            'accountId' => $wallet->getAccount()->getId(),
        ]);
    }

    #[Route('/account/{accountId}/wallet/{walletId}/transaction/new/category/{category}', name: 'new_transaction_wallet_with_category')]
    public function newForWalletWithCategory(Request $request, int $accountId, int $walletId, string $category): Response
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        $wallet = $this->entityAccessService->getWalletWithAccessCheck($walletId);
        if (!$wallet instanceof Wallet) {
            return $this->redirectToRoute('account_list');
        }

        $user = $wallet->getIndividual();
        $transaction = $this->walletTransactionCreationManager->beginTransactionWithWalletAndCategoryCreation($wallet, $user, $category);

        $form = $this->createForm(TransactionForWalletType::class, $transaction, [
            'transactionCategory' => $transaction->getTransactionCategory(),
            'wallet' => $wallet,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->walletTransactionCreationManager->saveTransactionWallet($transaction);

            return $this->redirectToWalletDashboard($wallet);
        }

        return $this->render('account/wallet/dashboard/transaction/new.html.twig', [
            'form' => $form,
            'wallet' => $wallet,
            'transaction' => $transaction,
            'accountId' => $wallet->getAccount()->getId(),
        ]);
    }

    #[Route('/account/{accountId}/wallet/{walletId}/transaction/{transactionId}/delete', name: 'delete_transaction_wallet')]
    public function delete(int $accountId, int $walletId, int $transactionId): RedirectResponse
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        $wallet = $this->entityAccessService->getWalletWithAccessCheck($walletId);
        if (!$wallet instanceof Wallet) {
            return $this->redirectToRoute('account_list');
        }

        $transaction = $this->entityAccessService->getTransactionWithAccessCheck($transactionId);
        if (!$transaction instanceof Transaction) {
            return $this->redirectToRoute('account_list');
        }

        $wallet = $transaction->getWallet();

        try {
            $this->walletTransactionManager->deleteTransaction($transaction);
            $this->addFlash('success', 'Transaction deleted successfully.');
        } catch (Exception $exception) {
            $this->addFlash('error', sprintf('Error deleting transaction: %s', $exception->getMessage()));
        }

        return $this->redirectToWalletDashboard($wallet);
    }

    #[Route('/account/{accountId}/wallet/{walletId}/transaction/{transactionId}/show', name: 'show_transaction_from_wallet')]
    public function viewTransaction(int $accountId, int $walletId, int $transactionId): Response
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        $wallet = $this->entityAccessService->getWalletWithAccessCheck($walletId);
        if (!$wallet instanceof Wallet) {
            return $this->redirectToRoute('account_list');
        }

        $transaction = $this->entityAccessService->getTransactionWithAccessCheck($transactionId);
        if (!$transaction instanceof Transaction) {
            return $this->redirectToRoute('account_list');
        }

        return $this->render('wallet/show.html.twig', [
            'transaction' => $transaction,
            'wallet' => $wallet,
            'account' => $wallet->getAccount(),
        ]);
    }

    #[Route('/account/{accountId}/wallet/{walletId}/transaction/{transactionId}/edit', name: 'edit_transaction_from_wallet')]
    public function editTransactionForWallet(int $accountId, int $walletId, int $transactionId, Request $request): Response
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        $wallet = $this->entityAccessService->getWalletWithAccessCheck($walletId);
        if (!$wallet instanceof Wallet) {
            return $this->redirectToRoute('account_list');
        }

        $transaction = $this->entityAccessService->getTransactionWithAccessCheck($transactionId);
        if (!$transaction instanceof Transaction) {
            return $this->redirectToRoute('account_list');
        }

        $wallet = $transaction->getWallet();

        $form = $this->createForm(TransactionForWalletType::class, $transaction, [
            'wallet' => $wallet,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Transaction updated successfully.');

            return $this->redirectToWalletDashboard($wallet);
        }

        return $this->render('account/wallet/dashboard/transaction/edit.html.twig', [
            'form' => $form,
            'transaction' => $transaction,
            'accountId' => $wallet->getAccount()->getId(),
        ]);
    }

    #[Route('/account/{accountId}/wallet/{walletId}/transaction/delete-category/{category}', name: 'delete_all_transactions_from_specific_category')]
    public function deleteTransactionCategory(int $accountId, int $walletId, string $category): RedirectResponse
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        $wallet = $this->entityAccessService->getWalletWithAccessCheck($walletId);
        if (!$wallet instanceof Wallet) {
            return $this->redirectToRoute('account_list');
        }

        $this->deleteTransactionsByCategory($wallet, $category);

        return $this->redirectToWalletDashboard($wallet);
    }

    /**
     * Redirect to wallet dashboard.
     */
    private function redirectToWalletDashboard(Wallet $wallet): RedirectResponse
    {
        return $this->redirectToRoute('account_wallet_dashboard', [
            'walletId' => $wallet->getId(),
            'accountId' => $wallet->getAccount()->getId(),
            'year' => $wallet->getYear(),
            'month' => $wallet->getMonth(),
        ]);
    }

    /**
     * Delete transactions by category and handle result.
     */
    private function deleteTransactionsByCategory(Wallet $wallet, string $category): void
    {
        $isDeleted = $this->walletTransactionDeleteManager->deleteTransactionsByCategory($wallet, $category);
        if (!$isDeleted) {
            $this->addFlash('warning', sprintf('No transactions found for the category %s.', $category));
        } else {
            $this->addFlash('success', sprintf('All transactions in category %s deleted successfully.', ucfirst($category)));
        }
    }
}
