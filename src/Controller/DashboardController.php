<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\WalletRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    public function __construct(private readonly WalletRepository $walletRepository) {}

    #[Route('/dashboard', name: 'dashboard')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $hasWallet = $this->walletRepository->userHasWallet($user);

        return $this->render('dashboard/index.html.twig', [
            'hasWallet' => $hasWallet,
        ]);
    }
}
