<?php

declare(strict_types=1);

namespace App\Controller\Transaction;

use App\Entity\User;
use App\Exception\UserHasNoWalletException;
use App\Form\Transaction\TransactionType;
use App\Manager\Refacto\Transaction\TransactionCreationManager;
use App\Service\User\UserCheckerService;
use App\Service\Wallet\WalletService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TransactionController extends AbstractController
{
    public function __construct(
        private readonly UserCheckerService $userCheckerService,
        private readonly WalletService $walletService,
        private readonly TransactionCreationManager $transactionCreationManager,
    ) {}

    #[Route('/transaction/new', name: 'new_transaction')]
    public function new(Request $request): Response
    {
        $user = $this->userCheckerService->getUserOrThrow();
        if (!$this->handleUserHasNoWallet($user)) {
            return $this->redirectToRoute('account_list');
        }

        $transaction = $this->transactionCreationManager->beginTransactionCreation();
        $form = $this->createForm(TransactionType::class, $transaction, [
            'user' => $user,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->transactionCreationManager->saveTransaction($transaction);

            return $this->redirectToRoute('account_wallet_dashboard', [
                'walletId' => $transaction->getWallet()->getId(),
                'accountId' => $transaction->getWallet()->getAccount()->getId(),
                'year' => $transaction->getWallet()->getYear(),
                'month' => $transaction->getWallet()->getMonth(),
            ]);
        }

        return $this->render('transaction/new.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Handle the case where the user has no wallet.
     */
    private function handleUserHasNoWallet(User $user): bool
    {
        try {
            $this->walletService->ensureUserHasWallet($user);

            return true;
        } catch (UserHasNoWalletException $userHasNoWalletException) {
            $this->addFlash('warning', $userHasNoWalletException->getMessage());

            return false;
        }
    }
}
