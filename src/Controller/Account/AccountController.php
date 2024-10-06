<?php

declare(strict_types=1);

namespace App\Controller\Account;

use App\Entity\Account;
use App\Exception\AccountAccessDeniedException;
use App\Exception\MaxAccountsReachedException;
use App\Form\Account\AccountType;
use App\Manager\Account\AccountManager;
use App\Manager\Wallet\WalletManager;
use App\Service\Account\Wallet\WalletService;
use App\Service\Checker\Account\AccountCheckerService;
use App\Service\User\UserCheckerService;
use App\Service\Voter\Account\AccountVoterService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

final class AccountController extends AbstractController
{
    public function __construct(
        private readonly WalletService $walletService,
        private readonly AccountManager $accountManager,
        private readonly AccountCheckerService $accountCheckerService,
        private readonly UserCheckerService $userCheckerService,
        private readonly WalletManager $walletManager,
        private readonly AccountVoterService $accountVoterService,
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
        if (!$this->checkAccountCreationPermissions()) {
            return $this->redirectToRoute('account_list');
        }

        $account = new Account();
        $user = $this->userCheckerService->getUserOrThrow();
        $account->setIndividual($user);
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

    #[Route('/account/{accountId}/edit', name: 'account_edit')]
    public function edit(int $accountId, Request $request): Response
    {
        $account = $this->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
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
    #[Route('/account/{accountId}/delete', name: 'account_delete')]
    public function delete(int $accountId): Response
    {
        $account = $this->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        $this->accountManager->deleteAccount($accountId);

        return $this->redirectToRoute('account_list');
    }

    #[\Symfony\Component\Routing\Attribute\Route('/account/{accountId}/delete/{year}', name: 'account_year_delete')]
    public function deleteYearlyWallet(int $accountId, int $year): RedirectResponse
    {
        $account = $this->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        try {
            $this->walletManager->deleteWalletsForYear($account, $year);
            $this->addFlash('success', sprintf('The year %d and all its wallets and transactions were deleted successfully.', $year));
        } catch (NotFoundResourceException $exception) {
            $this->addFlash('warning', $exception->getMessage());
        } catch (Exception $exception) {
            $this->addFlash('error', sprintf('An error occurred while deleting the wallets: %s', $exception->getMessage()));
        }

        return $this->redirectToRoute('account_list');
    }

    /**
     * Get the account with access check.
     */
    private function getAccountWithAccessCheck(int $accountId): ?Account
    {
        try {
            $account = $this->accountCheckerService->getAccountOrThrow($accountId);
            $this->accountVoterService->canAccessAccount($account);

            return $account;
        } catch (AccountAccessDeniedException $accountAccessDeniedException) {
            $this->addFlash('error', $accountAccessDeniedException->getMessage());

            return null;
        }
    }

    /**
     * Verify if the user has the permission to create an account.
     */
    private function checkAccountCreationPermissions(): bool
    {
        try {
            $this->accountVoterService->canCreateAccount();

            return true;
        } catch (MaxAccountsReachedException $maxAccountsReachedException) {
            $this->addFlash('error', $maxAccountsReachedException->getMessage());

            return false;
        }
    }
}
