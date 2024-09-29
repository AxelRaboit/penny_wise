<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Wallet;
use App\Form\TransactionForWalletType;
use App\Manager\TransactionManager;
use App\Repository\TransactionRepository;
use App\Repository\WalletRepository;
use App\Security\Voter\Transaction\UserCanAccessTransactionVoter;
use App\Security\Voter\Wallet\UserCanAccessWalletVoter;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TransactionForWalletController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WalletRepository $walletRepository,
        private readonly TransactionRepository $transactionRepository,
        private readonly TransactionManager $transactionManager,
    ) {}

    #[Route('/wallet/transaction/new/wallet/{id}', name: 'new_transaction_for_wallet')]
    public function newForWallet(int $id, Request $request): Response
    {
        $wallet = $this->walletRepository->find($id);
        if (!$wallet instanceof Wallet) {
            throw $this->createNotFoundException('Wallet not found');
        }

        if (!$this->isGranted(UserCanAccessWalletVoter::ACCESS_WALLET, $wallet)) {
            throw $this->createAccessDeniedException('You are not allowed to create a transaction for this wallet.');
        }

        /** @var User $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found.');
        }

        $transaction = new Transaction();
        $transaction->setWallet($wallet);
        $transaction->setIndividual($user);

        $form = $this->createForm(TransactionForWalletType::class, $transaction, [
            'wallet' => $wallet,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->transactionManager->handleTransactionTags($transaction);
            $this->entityManager->persist($transaction);
            $this->entityManager->flush();

            return $this->redirectToRoute('monthly_wallet', [
                'year' => $wallet->getYear(),
                'month' => $wallet->getMonth(),
            ]);
        }

        return $this->render('transaction/forWallet/new_for_wallet.html.twig', [
            'form' => $form,
            'wallet' => $wallet,
            'transaction' => $transaction,
        ]);
    }

    #[Route('/wallet/transaction/delete/{id}', name: 'delete_transaction')]
    public function delete(int $id): RedirectResponse
    {
        $transaction = $this->transactionRepository->find($id);
        if (!$transaction instanceof Transaction) {
            throw $this->createNotFoundException('Transaction not found.');
        }

        $wallet = $transaction->getWallet();

        if (!$this->isGranted(UserCanAccessTransactionVoter::ACCESS_TRANSACTION, $transaction)) {
            throw $this->createAccessDeniedException('You are not allowed to delete this transaction.');
        }

        try {
            $this->entityManager->remove($transaction);
            $this->entityManager->flush();
            $this->addFlash('success', 'Transaction deleted successfully.');
        } catch (Exception $exception) {
            $this->addFlash('error', sprintf('Error deleting transaction: %s', $exception->getMessage()));

            return $this->redirectToRoute('monthly_wallet', [
                'year' => $wallet->getYear(),
                'month' => $wallet->getMonth(),
            ]);
        }

        return $this->redirectToRoute('monthly_wallet', [
            'year' => $wallet->getYear(),
            'month' => $wallet->getMonth(),
        ]);
    }

    #[Route('/wallet/transaction/edit/{id}', name: 'edit_transaction_from_wallet')]
    public function editTransactionForWallet(int $id, Request $request): Response
    {
        $transaction = $this->transactionRepository->find($id);
        if (!$transaction instanceof Transaction) {
            throw $this->createNotFoundException('Transaction not found.');
        }

        $wallet = $transaction->getWallet();

        if (!$this->isGranted(UserCanAccessTransactionVoter::ACCESS_TRANSACTION, $transaction)) {
            throw $this->createAccessDeniedException('You are not allowed to edit this transaction.');
        }

        /** @var User $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found.');
        }

        $wallet = $this->walletRepository->findWalletByUser($user, $wallet->getYear(), $wallet->getMonth());
        if (!$wallet instanceof Wallet) {
            throw $this->createNotFoundException('Wallet not found for the specified year and month.');
        }

        $form = $this->createForm(TransactionForWalletType::class, $transaction, [
            'wallet' => $wallet,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Transaction updated successfully.');

            return $this->redirectToRoute('monthly_wallet', [
                'year' => $transaction->getWallet()->getYear(),
                'month' => $transaction->getWallet()->getMonth(),
            ]);
        }

        return $this->render('transaction/forWallet/edit_for_wallet.html.twig', [
            'form' => $form,
            'transaction' => $transaction,
        ]);
    }

    #[Route('/wallet/{id}/transaction/delete-category/{category}', name: 'delete_all_transactions_from_specific_category')]
    public function deleteTransactionCategory(int $id, string $category): RedirectResponse
    {
        $wallet = $this->walletRepository->find($id);
        if (!$wallet instanceof Wallet) {
            throw $this->createNotFoundException('Wallet not found');
        }

        if (!$this->isGranted(UserCanAccessWalletVoter::ACCESS_WALLET, $wallet)) {
            throw $this->createAccessDeniedException('You are not allowed to delete transactions from this wallet.');
        }

        $isDeleted = $this->transactionManager->deleteTransactionsByCategory($wallet, $category);
        if (!$isDeleted) {
            $this->addFlash('warning', sprintf('No transactions found for the category %s.', $category));
        } else {
            $this->addFlash('success', sprintf('All transactions in category %s deleted successfully.', ucfirst($category)));
        }

        return $this->redirectToRoute('monthly_wallet', [
            'year' => $wallet->getYear(),
            'month' => $wallet->getMonth(),
        ]);
    }
}
