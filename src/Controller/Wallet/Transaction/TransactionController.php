<?php

declare(strict_types=1);

namespace App\Controller\Wallet\Transaction;

use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Wallet;
use App\Exception\TransactionAccessDeniedException;
use App\Exception\WalletAccessDeniedException;
use App\Form\Transaction\TransactionForWalletType;
use App\Manager\Account\Wallet\Transaction\TransactionWalletDeleteManager;
use App\Manager\Account\Wallet\Transaction\TransactionWalletManager;
use App\Manager\Account\Wallet\Transaction\TransactionWalletCreationManager;
use App\Manager\Transaction\TransactionManager;
use App\Repository\Wallet\WalletRepository;
use App\Security\Voter\Transaction\TransactionVoter;
use App\Service\Checker\Wallet\Transaction\TransactionCheckerService;
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
        private readonly EntityManagerInterface           $entityManager,
        private readonly TransactionCheckerService        $transactionCheckerService,
        private readonly TransactionManager               $transactionManager,
        private readonly TransactionWalletDeleteManager   $transactionWalletDeleteManager,
        private readonly TransactionWalletCreationManager $transactionWalletCreationManager,
        private readonly WalletVoterService               $walletVoterService,
        private readonly WalletCheckerService             $walletCheckerService,
        private readonly TransactionVoterService          $transactionVoterService,
    ) {}

    #[Route('/account/{accountId}/wallet/{walletId}/transaction/new', name: 'new_transaction_for_wallet')]
    public function newForWallet(int $accountId, int $walletId, Request $request): Response
    {
        $wallet = $this->getWalletWithAccessCheck($walletId);
        if (!$wallet) {
            return $this->redirectToRoute('account_list');
        }

        $user  = $wallet->getIndividual();

        $transaction = $this->transactionWalletCreationManager->beginTransactionCreationWithWallet($wallet, $user);

        $form = $this->createForm(TransactionForWalletType::class, $transaction, [
            'wallet' => $wallet,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->transactionWalletCreationManager->saveTransactionWallet($transaction);

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
        if (!$wallet) {
            return $this->redirectToRoute('account_list');
        }

        $user  = $wallet->getIndividual();
        $transaction = $this->transactionWalletCreationManager->beginTransactionWithWalletAndCategoryCreation($wallet, $user, $category);

        $form = $this->createForm(TransactionForWalletType::class, $transaction, [
            'transactionCategory' => $transaction->getTransactionCategory(),
            'wallet' => $wallet,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->transactionWalletCreationManager->saveTransactionWallet($transaction);

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
            $this->transactionManager->deleteTransaction($transaction);
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
        if (!$transaction) {
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
        if (!$wallet) {
            return $this->redirectToRoute('account_list');
        }

        $isDeleted = $this->transactionWalletDeleteManager->deleteTransactionsByCategory($wallet, $category);
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
     *
     * @param int $walletId
     * @return Wallet|null
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
     *
     * @param int $transactionId
     * @return Transaction|null
     */
    private function getTransactionWithAccessCheck(int $transactionId): ?Transaction
    {
        try {
            $transaction = $this->transactionCheckerService->getTransactionOrThrow($transactionId);
            $this->transactionVoterService->canAccessTransaction($transaction);
            return $transaction;
        } catch (TransactionAccessDeniedException $exception) {
            $this->addFlash('error', 'You are not allowed to edit this transaction.');
            return null;
        }
    }
}
