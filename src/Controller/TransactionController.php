<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionForWalletType;
use App\Form\TransactionType;
use App\Repository\TransactionRepository;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TransactionController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly WalletRepository $walletRepository, private readonly TransactionRepository $transactionRepository) {}

    #[Route('/transaction/new', name: 'transaction_new')]
    public function new(Request $request): Response
    {
        $transaction = new Transaction();
        $form = $this->createForm(TransactionType::class, $transaction);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($transaction);
            $this->entityManager->flush();

            return $this->redirectToRoute('monthly_wallet', [
                'year' => $transaction->getWallet()->getYear(),
                'month' => $transaction->getWallet()->getMonth(),
            ]);
        }

        return $this->render('transaction/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/transaction/new/{year}/{month}', name: 'transaction_new_for_wallet')]
    public function newForWallet(int $year, int $month, Request $request): Response
    {
        $wallet = $this->walletRepository
            ->findOneBy(['year' => $year, 'month' => $month]);

        if (null === $wallet) {
            throw $this->createNotFoundException('Wallet not found for the specified year and month.');
        }

        $transaction = new Transaction();
        $transaction->setWallet($wallet);

        $form = $this->createForm(TransactionForWalletType::class, $transaction, [
            'wallet' => $wallet,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($transaction);
            $this->entityManager->flush();

            return $this->redirectToRoute('monthly_wallet', [
                'year' => $year,
                'month' => $month,
            ]);
        }

        return $this->render('transaction/new_for_wallet.html.twig', [
            'form' => $form,
            'wallet' => $wallet,
        ]);
    }

    #[Route('/transaction/delete/{year}/{month}/{id}', name: 'transaction_delete')]
    public function delete(int $year, int $month, int $id): RedirectResponse
    {
        $transaction = $this->transactionRepository->find($id);
        if (null === $transaction) {
            throw $this->createNotFoundException('Transaction not found.');
        }

        try {
            $this->entityManager->remove($transaction);
            $this->entityManager->flush();
            $this->addFlash('success', 'Transaction deleted successfully.');
        } catch (Exception $exception) {
            $this->addFlash('error', sprintf('Error deleting transaction: %s', $exception->getMessage()));

            return $this->redirectToRoute('monthly_wallet', [
                'year' => $year,
                'month' => $month,
            ]);
        }

        return $this->redirectToRoute('monthly_wallet', [
            'year' => $year,
            'month' => $month,
        ]);
    }

    #[Route('/transaction/edit/{id}/{year}/{month}', name: 'transaction_edit_for_wallet')]
    public function editTransactionForWallet(Request $request, Transaction $transaction, int $year, int $month): Response
    {
        $wallet = $this->walletRepository
            ->findOneBy(['year' => $year, 'month' => $month]);

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

        return $this->render('transaction/edit_for_wallet.html.twig', [
            'form' => $form,
            'transaction' => $transaction,
        ]);
    }
}
