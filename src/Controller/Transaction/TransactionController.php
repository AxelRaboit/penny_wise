<?php

declare(strict_types=1);

namespace App\Controller\Transaction;

use App\Entity\Transaction;
use App\Entity\User;
use App\Form\Transaction\TransactionType;
use App\Manager\Transaction\TransactionManager;
use App\Repository\Wallet\WalletRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TransactionController extends AbstractController
{
    public function __construct(
        private readonly WalletRepository $walletRepository,
        private readonly TransactionManager $transactionManager,
    ) {}

    #[Route('/transaction/new', name: 'new_transaction')]
    public function new(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found.');
        }

        $hasWallet = $this->walletRepository->userHasWallet($user);
        if (!$hasWallet) {
            $this->addFlash('warning', 'You need to create a wallet first.');

            return $this->redirectToRoute('account_list');
        }

        $transaction = new Transaction();
        $form = $this->createForm(TransactionType::class, $transaction, [
            'user' => $user,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->transactionManager->saveTransaction($transaction);

            return $this->redirectToRoute('monthly_wallet', [
                'year' => $transaction->getWallet()->getYear(),
                'month' => $transaction->getWallet()->getMonth(),
            ]);
        }

        return $this->render('transaction/new.html.twig', [
            'form' => $form,
        ]);
    }
}