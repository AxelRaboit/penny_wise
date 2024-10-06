<?php

declare(strict_types=1);

namespace App\Controller\Account\Wallet\Transaction;

use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Exception\TransactionAccessDeniedException;
use App\Exception\WalletAccessDeniedException;
use App\Form\Transaction\TransactionForWalletType;
use App\Manager\Refacto\Account\Wallet\Transaction\WalletTransactionCreationManager;
use App\Manager\Refacto\Account\Wallet\Transaction\WalletTransactionDeleteManager;
use App\Manager\Refacto\Account\Wallet\Transaction\WalletTransactionManager;
use App\Security\Voter\Transaction\TransactionVoter;
use App\Service\Checker\Transaction\TransactionCheckerService;
use App\Service\Checker\Wallet\WalletCheckerService;
use App\Service\Voter\Account\Wallet\Transaction\TransactionVoterService;
use App\Service\Voter\Account\Wallet\WalletVoterService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TransactionController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TransactionCheckerService $transactionCheckerService,
        private readonly WalletTransactionManager $walletTransactionManager,
        private readonly WalletTransactionDeleteManager $walletTransactionDeleteManager,
        private readonly WalletTransactionCreationManager $walletTransactionCreationManager,
        private readonly WalletVoterService $walletVoterService,
        private readonly WalletCheckerService $walletCheckerService,
        private readonly TransactionVoterService $transactionVoterService,
    ) {}

    #[Route('/account/{accountId}/wallet/{walletId}/transaction/new', name: 'new_transaction_for_wallet')]
    public function newForWallet(int $accountId, int $walletId, Request $request): Response
    {
        $wallet = $this->getWalletWithAccessCheck($walletId);
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

            return $this->redirectToRoute('account_wallet_dashboard', [
                'accountId' => $wallet->getAccount()->getId(),
                'year' => $wallet->getYear(),
                'month' => $wallet->getMonth(),
            ]);
        }

        return $this->render('transaction/forWallet/new_for_wallet.html.twig', [
            'form' => $form,
            'wallet' => $wallet,
            'transaction' => $transaction,
            'accountId' => $wallet->getAccount()->getId(),
        ]);
    }

    #[Route('/wallet/{walletId}/transaction/new/category/{category}', name: 'new_transaction_for_wallet_with_category')]
    public function newForWalletWithCategory(Request $request, int $walletId, string $category): Response
    {
        $wallet = $this->getWalletWithAccessCheck($walletId);
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

            return $this->redirectToRoute('account_wallet_dashboard', [
                'accountId' => $wallet->getAccount()->getId(),
                'year' => $transaction->getWallet()->getYear(),
                'month' => $transaction->getWallet()->getMonth(),
            ]);
        }

        return $this->render('transaction/forWallet/new_for_wallet.html.twig', [
            'form' => $form,
            'wallet' => $wallet,
            'transaction' => $transaction,
            'accountId' => $wallet->getAccount()->getId(),
        ]);
    }

    #[Route('/wallet/transaction/{id}/delete', name: 'delete_transaction')]
    public function delete(int $id): RedirectResponse
    {
        $transaction = $this->transactionCheckerService->getTransactionOrThrow($id);

        $wallet = $transaction->getWallet();

        if (!$this->isGranted(TransactionVoter::ACCESS_TRANSACTION, $transaction)) {
            throw $this->createAccessDeniedException('You are not allowed to delete this transaction.');
        }

        try {
            $this->walletTransactionManager->deleteTransaction($transaction);
            $this->addFlash('success', 'Transaction deleted successfully.');
        } catch (Exception $exception) {
            $this->addFlash('error', sprintf('Error deleting transaction: %s', $exception->getMessage()));

            return $this->redirectToRoute('account_wallet_dashboard', [
                'accountId' => $wallet->getAccount()->getId(),
                'year' => $wallet->getYear(),
                'month' => $wallet->getMonth(),
            ]);
        }

        return $this->redirectToRoute('account_wallet_dashboard', [
            'accountId' => $wallet->getAccount()->getId(),
            'year' => $wallet->getYear(),
            'month' => $wallet->getMonth(),
        ]);
    }

    #[Route('/wallet/transaction/{transactionId}/edit', name: 'edit_transaction_from_wallet')]
    public function editTransactionForWallet(int $transactionId, Request $request): Response
    {
        $transaction = $this->getTransactionWithAccessCheck($transactionId);
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

            return $this->redirectToRoute('account_wallet_dashboard', [
                'accountId' => $wallet->getAccount()->getId(),
                'year' => $transaction->getWallet()->getYear(),
                'month' => $transaction->getWallet()->getMonth(),
            ]);
        }

        return $this->render('transaction/forWallet/edit_for_wallet.html.twig', [
            'form' => $form,
            'transaction' => $transaction,
            'accountId' => $wallet->getAccount()->getId(),
        ]);
    }

    #[Route('/wallet/{id}/transaction/delete-category/{category}', name: 'delete_all_transactions_from_specific_category')]
    public function deleteTransactionCategory(int $id, string $category): RedirectResponse
    {
        $wallet = $this->getWalletWithAccessCheck($id);
        if (!$wallet instanceof Wallet) {
            return $this->redirectToRoute('account_list');
        }

        $isDeleted = $this->walletTransactionDeleteManager->deleteTransactionsByCategory($wallet, $category);
        if (!$isDeleted) {
            $this->addFlash('warning', sprintf('No transactions found for the category %s.', $category));
        } else {
            $this->addFlash('success', sprintf('All transactions in category %s deleted successfully.', ucfirst($category)));
        }

        return $this->redirectToRoute('account_wallet_dashboard', [
            'accountId' => $wallet->getAccount()->getId(),
            'year' => $wallet->getYear(),
            'month' => $wallet->getMonth(),
        ]);
    }

    /**
     * Get the wallet with access check.
     */
    private function getWalletWithAccessCheck(int $walletId): ?Wallet
    {
        try {
            $wallet = $this->walletCheckerService->getWalletByIdOrThrow($walletId);
            $this->walletVoterService->canAccessWallet($wallet);

            return $wallet;
        } catch (WalletAccessDeniedException) {
            $this->addFlash('error', 'You are not allowed to delete transactions from this wallet.');

            return null;
        }
    }

    /**
     * Get the transaction with access check.
     */
    private function getTransactionWithAccessCheck(int $transactionId): ?Transaction
    {
        try {
            $transaction = $this->transactionCheckerService->getTransactionOrThrow($transactionId);
            $this->transactionVoterService->canAccessTransaction($transaction);

            return $transaction;
        } catch (TransactionAccessDeniedException) {
            $this->addFlash('error', 'You are not allowed to edit this transaction.');

            return null;
        }
    }
}
