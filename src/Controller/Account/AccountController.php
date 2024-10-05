<?php

declare(strict_types=1);

namespace App\Controller\Account;

use App\Entity\Account;
use App\Exception\MaxAccountsReachedException;
use App\Form\Account\AccountType;
use App\Manager\Account\AccountManager;
use App\Security\Voter\Account\AccountVoter;
use App\Service\Account\AccountCheckerService;
use App\Service\User\UserCheckerService;
use App\Service\WalletService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class AccountController extends AbstractController
{
    public function __construct(
        private readonly WalletService $walletService,
        private readonly AccountManager $accountManager,
        private readonly AccountCheckerService $accountCheckerService,
        private readonly UserCheckerService $userCheckerService,
    ) {}

    #[Route('/', name: 'account_list')]
    public function index(): Response
    {
        $user = $this->userCheckerService->getUserOrThrow();
        $accounts = $this->walletService->findAllAccountsWithWalletsByUser($user);

        return $this->render('account/account_list.html.twig', [
            'accounts' => $accounts,
        ]);
    }

    #[Route('/account/new', name: 'account_new')]
    public function new(Request $request): Response
    {
        try {
            if (!$this->isGranted(AccountVoter::CREATE_ACCOUNT)) {
                throw new MaxAccountsReachedException();
            }
        } catch (MaxAccountsReachedException $maxAccountsReachedException) {
            $this->addFlash('error', $maxAccountsReachedException->getMessage());

            return $this->redirectToRoute('account_list');
        }

        $account = new Account();
        $form = $this->createForm(AccountType::class, $account);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->accountManager->createAccount($account);

            return $this->redirectToRoute('account_list');
        }

        return $this->render('account/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/account/{id}/edit', name: 'account_edit')]
    public function edit(int $id, Request $request): Response
    {
        $account = $this->accountCheckerService->getAccountOrThrow($id);
        if (!$this->isGranted(AccountVoter::ACCESS_ACCOUNT, $account)) {
            throw $this->createAccessDeniedException('You do not have permission to edit this account');
        }

        $form = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->accountManager->updateAccount($account);

            return $this->redirectToRoute('account_list');
        }

        return $this->render('account/edit.html.twig', [
            'form' => $form->createView(),
            'account' => $account,
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/account/{id}/delete', name: 'account_delete')]
    public function delete(int $id): Response
    {
        $account = $this->accountCheckerService->getAccountOrThrow($id);
        if (!$this->isGranted(AccountVoter::ACCESS_ACCOUNT, $account)) {
            throw $this->createAccessDeniedException('You do not have permission to delete this account');
        }

        $this->accountManager->deleteAccount($id);

        return $this->redirectToRoute('account_list');
    }
}
