<?php

declare(strict_types=1);

namespace App\Controller\AccountList;

use App\Entity\Account;
use App\Exception\AccountAccessDeniedException;
use App\Exception\MaxAccountsReachedException;
use App\Form\Account\AccountType;
use App\Form\Account\Wallet\WalletType;
use App\Form\Wallet\WalletCreateWithPreselectedMonthType;
use App\Manager\Refacto\AccountList\AccountListWalletManager;
use App\Manager\Refacto\AccountList\Wallet\AccountListWalletCreationManager;
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

final class AccountListController extends AbstractController
{
    public function __construct(
        private readonly WalletService $walletService,
        private readonly AccountCheckerService $accountCheckerService,
        private readonly UserCheckerService $userCheckerService,
        private readonly AccountVoterService $accountVoterService,
        private readonly AccountListWalletManager $accountListWalletManager,
        private readonly AccountListWalletCreationManager $accountListWalletCreationManager,
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
    public function newAccount(Request $request): Response
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
            $this->accountListWalletManager->createAccount($account);

            return $this->redirectToRoute('account_list');
        }

        return $this->render('account/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/account/{id}/edit', name: 'account_edit')]
    public function editAccount(int $id, Request $request): Response
    {
        $account = $this->getAccountWithAccessCheck($id);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        $form = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->accountListWalletManager->updateAccount($account);

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
    public function deleteAccount(int $id): Response
    {
        $account = $this->getAccountWithAccessCheck($id);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        $this->accountListWalletManager->deleteAccount($account);

        return $this->redirectToRoute('account_list');
    }

    #[Route('/account/{id}/delete/{year}', name: 'account_year_delete')]
    public function deleteYearAccount(int $id, int $year): RedirectResponse
    {
        $account = $this->getAccountWithAccessCheck($id);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        try {
            $this->accountListWalletManager->deleteWalletsForYear($account, $year);
            $this->addFlash('success', sprintf('The year %d and all its wallets and transactions were deleted successfully.', $year));
        } catch (NotFoundResourceException $exception) {
            $this->addFlash('warning', $exception->getMessage());
        } catch (Exception $exception) {
            $this->addFlash('error', sprintf('An error occurred while deleting the wallets: %s', $exception->getMessage()));
        }

        return $this->redirectToRoute('account_list');
    }

    #[Route('/account/{id}/wallet/new', name: 'account_new_wallet')]
    public function newWalletAccount(Request $request, int $id): Response
    {
        $account = $this->getAccountWithAccessCheck($id);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        $wallet = $this->accountListWalletCreationManager->beginWalletCreation($account);

        $form = $this->createForm(WalletType::class, $wallet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->accountListWalletCreationManager->endWalletCreation($wallet);

            return $this->redirectToRoute('account_list');
        }

        return $this->render('account/wallet/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/account/{id}/wallet/new/{year}/{month}', name: 'account_wallet_new_for_year_month')]
    public function newWalletForYearMonth(int $id, int $year, int $month, Request $request): Response
    {
        /*
         * TODO AXEL: Faire en sorte de ne pas avoir besoin de {month} car on créer le next month
         * Donc récuperer le dernier wallet lié au account et à l'année, par exemple si le dernier mois du account & year est Novembre, alors on créer Décembre
         * et l'url serait /account/{id}/wallet/new/{year}/next-month
        */

        $account = $this->getAccountWithAccessCheck($id);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        $wallet = $this->accountListWalletCreationManager->beginWalletYearCreationWithMonth($account, $year, $month);

        $form = $this->createForm(WalletCreateWithPreselectedMonthType::class, $wallet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->accountListWalletCreationManager->endWalletCreation($wallet);

            return $this->redirectToRoute('account_list');
        }

        return $this->render('wallet/walletForAccount/new_wallet_for_year.html.twig', [
            'form' => $form,
            'wallet' => $wallet,
        ]);
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
