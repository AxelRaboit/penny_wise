<?php

declare(strict_types=1);

namespace App\Controller\Account\Wallet\Transaction;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Form\Transaction\TransactionForWalletType;
use App\Manager\Account\Wallet\Transaction\WalletTransactionCreationManager;
use App\Manager\Account\Wallet\Transaction\WalletTransactionDeleteManager;
use App\Manager\Account\Wallet\Transaction\WalletTransactionManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class TransactionWalletController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WalletTransactionManager $walletTransactionManager,
        private readonly WalletTransactionDeleteManager $walletTransactionDeleteManager,
        private readonly WalletTransactionCreationManager $walletTransactionCreationManager,
    ) {}

    #[Route('/account/{account}/wallet/{wallet}/transaction/new', name: 'new_transaction_wallet')]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    #[IsGranted('ACCESS_WALLET', subject: 'wallet')]
    public function newForWallet(Account $account, Wallet $wallet, Request $request): Response
    {
        $user = $wallet->getUser();

        $transaction = $this->walletTransactionCreationManager->beginTransactionCreationWithWallet($wallet, $user);

        $form = $this->createForm(TransactionForWalletType::class, $transaction, [
            'wallet' => $wallet,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->walletTransactionCreationManager->saveTransactionWallet($transaction);

            return $this->redirectToWalletDashboard($wallet, $account);
        }

        return $this->render('account/wallet/dashboard/transaction/new.html.twig', [
            'form' => $form,
            'wallet' => $wallet,
            'transaction' => $transaction,
            'accountId' => $wallet->getAccount()->getId(),
        ]);
    }

    #[Route('/account/{account}/wallet/{wallet}/transaction/new/category/{category}', name: 'new_transaction_wallet_with_category')]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    #[IsGranted('ACCESS_WALLET', subject: 'wallet')]
    public function newForWalletWithCategory(Request $request, Account $account, Wallet $wallet, string $category): Response
    {
        $user = $wallet->getUser();
        $transaction = $this->walletTransactionCreationManager->beginTransactionWithWalletAndCategoryCreation($wallet, $user, $category);

        $form = $this->createForm(TransactionForWalletType::class, $transaction, [
            'transactionCategory' => $transaction->getTransactionCategory(),
            'wallet' => $wallet,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->walletTransactionCreationManager->saveTransactionWallet($transaction);

            return $this->redirectToWalletDashboard($wallet, $account);
        }

        return $this->render('account/wallet/dashboard/transaction/new.html.twig', [
            'form' => $form,
            'wallet' => $wallet,
            'transaction' => $transaction,
            'accountId' => $wallet->getAccount()->getId(),
        ]);
    }

    #[Route('/account/{account}/wallet/{wallet}/transaction/{transaction}/delete', name: 'delete_transaction_wallet')]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    #[IsGranted('ACCESS_WALLET', subject: 'wallet')]
    #[IsGranted('ACCESS_TRANSACTION', subject: 'transaction')]
    public function delete(Account $account, Wallet $wallet, Transaction $transaction): RedirectResponse
    {
        try {
            $this->walletTransactionManager->deleteTransaction($transaction);
            $this->addFlash('success', 'Transaction deleted successfully.');
        } catch (Exception $exception) {
            $this->addFlash('error', sprintf('Error deleting transaction: %s', $exception->getMessage()));
        }

        return $this->redirectToWalletDashboard($wallet, $account);
    }

    #[Route('/account/{account}/wallet/{wallet}/transaction/{transaction}/show', name: 'show_transaction_from_wallet')]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    #[IsGranted('ACCESS_WALLET', subject: 'wallet')]
    #[IsGranted('ACCESS_TRANSACTION', subject: 'transaction')]
    public function viewTransaction(Account $account, Wallet $wallet, Transaction $transaction): Response
    {
        return $this->render('account/wallet/dashboard/transaction/show.html.twig', [
            'transaction' => $transaction,
            'wallet' => $wallet,
            'account' => $account,
        ]);
    }

    #[Route('/account/{account}/wallet/{wallet}/transaction/{transaction}/edit', name: 'edit_transaction_from_wallet')]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    #[IsGranted('ACCESS_WALLET', subject: 'wallet')]
    #[IsGranted('ACCESS_TRANSACTION', subject: 'transaction')]
    public function editTransactionForWallet(Account $account, Wallet $wallet, Transaction $transaction, Request $request): Response
    {
        $form = $this->createForm(TransactionForWalletType::class, $transaction, [
            'wallet' => $wallet,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Transaction updated successfully.');

            return $this->redirectToWalletDashboard($wallet, $account);
        }

        return $this->render('account/wallet/dashboard/transaction/edit.html.twig', [
            'form' => $form,
            'transaction' => $transaction,
            'accountId' => $wallet->getAccount()->getId(),
        ]);
    }

    #[Route('/account/{account}/wallet/{wallet}/transaction/delete-category/{category}', name: 'delete_all_transactions_from_specific_category')]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    #[IsGranted('ACCESS_WALLET', subject: 'wallet')]
    public function deleteTransactionCategory(Account $account, Wallet $wallet, string $category): RedirectResponse
    {
        $this->deleteTransactionsByCategory($wallet, $category);

        return $this->redirectToWalletDashboard($wallet, $account);
    }

    /**
     * Redirect to wallet dashboard.
     */
    private function redirectToWalletDashboard(Wallet $wallet, Account $account): RedirectResponse
    {
        return $this->redirectToRoute('account_wallet_dashboard', [
            'wallet' => $wallet->getId(),
            'account' => $account->getId(),
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
            $this->addFlash('warning', sprintf('No transactions found for the category "%s".', $category));
        } else {
            $this->addFlash('success', sprintf('All transactions in the category "%s" deleted successfully.', ucfirst($category)));
        }
    }
}
