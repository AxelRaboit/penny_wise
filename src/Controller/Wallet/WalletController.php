<?php

declare(strict_types=1);

namespace App\Controller\Wallet;

use App\Entity\User;
use App\Entity\Wallet;
use App\Form\Wallet\WalletType;
use App\Form\Wallet\WalletUpdateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WalletController extends AbstractController
{
    private const string NEW_WALLET_TEMPLATE = 'wallet/new.html.twig';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    #[Route('/wallet/new', name: 'wallet_new')]
    public function new(Request $request): Response
    {
        $wallet = new Wallet();

        $form = $this->createForm(WalletType::class, $wallet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            $wallet->setIndividual($user);

            $this->entityManager->persist($wallet);
            $this->entityManager->flush();

            return $this->redirectToRoute('dashboard');
        }

        return $this->render(self::NEW_WALLET_TEMPLATE, [
            'form' => $form,
        ]);
    }

    #[Route('/wallet/edit/{id}', name: 'wallet_edit', methods: ['GET', 'POST'])]
    public function editWallet(Wallet $wallet, Request $request): Response
    {
        // TODO AXEL: Check if user is allowed to edit this wallet

        $form = $this->createForm(WalletUpdateType::class, $wallet);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('account_wallet_dashboard', [
                'accountId' => $wallet->getAccount()->getId(),
                'year' => $wallet->getYear(),
                'month' => $wallet->getMonth(),
            ]);
        }

        return $this->render('wallet/edit.html.twig', [
            'form' => $form,
            'wallet' => $wallet,
        ]);
    }
}
