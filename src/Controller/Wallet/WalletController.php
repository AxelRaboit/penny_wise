<?php

declare(strict_types=1);

namespace App\Controller\Wallet;

use App\Entity\User;
use App\Entity\Wallet;
use App\Form\Wallet\WalletType;
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
}
