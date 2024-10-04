<?php

declare(strict_types=1);

namespace App\Controller\Account;

use App\Entity\Account;
use App\Entity\User;
use App\Form\Account\AccountType;
use App\Service\WalletService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class AccountController extends AbstractController
{
    private const string ACCOUNT_LIST_TEMPLATE = 'account/account_list.html.twig';

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly WalletService $walletService) {}

    #[Route('/', name: 'account_list')]
    public function index(): Response
    {
        $user = $this->getUserOrThrow();
        $accounts = $this->walletService->findAllAccountsWithWalletsByUser($user);

        return $this->render(self::ACCOUNT_LIST_TEMPLATE, [
            'accounts' => $accounts,
        ]);
    }

    #[Route('/account/new', name: 'account_new')]
    public function new(Request $request): Response
    {
        $account = new Account();
        $form = $this->createForm(AccountType::class, $account);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            $account->setIndividual($user);
            $this->entityManager->persist($account);
            $this->entityManager->flush();

            return $this->redirectToRoute('account_list');
        }

        return $this->render('account/new.html.twig', [
            'form' => $form,
        ]);
    }

    private function getUserOrThrow(): User
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found');
        }

        return $user;
    }
}
