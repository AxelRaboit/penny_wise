<?php

declare(strict_types=1);

namespace App\Controller\AccountList;

use App\Entity\Account;
use App\Entity\Wallet;
use App\Enum\Wallet\MonthEnum;
use App\Exception\MaxAccountsReachedException;
use App\Form\Account\AccountType;
use App\Form\Account\Wallet\WalletType;
use App\Form\Wallet\WalletCreateWithPreselectedMonthType;
use App\Manager\Refacto\Account\Wallet\AccountWalletManager;
use App\Manager\Refacto\AccountList\AccountListWalletManager;
use App\Manager\Refacto\AccountList\Wallet\AccountListWalletCreationManager;
use App\Service\Account\Wallet\WalletService;
use App\Service\Checker\Account\AccountPermissionService;
use App\Service\EntityAccessService;
use App\Service\User\UserCheckerService;
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
        private readonly AccountPermissionService $accountPermissionService,
        private readonly UserCheckerService $userCheckerService,
        private readonly AccountListWalletManager $accountListWalletManager,
        private readonly AccountListWalletCreationManager $accountListWalletCreationManager,
        private readonly EntityAccessService $entityAccessService,
        private readonly AccountWalletManager $accountWalletManager,
    ) {}

    #[Route('/', name: 'account_list')]
    public function index(): Response
    {
        $user = $this->userCheckerService->getUserOrThrow();
        $accounts = $this->walletService->findAllAccountsWithWalletsByUser($user);

        return $this->render('accountList/list.html.twig', [
            'accounts' => $accounts,
        ]);
    }

    #[Route('/account/new', name: 'account_new')]
    public function newAccount(Request $request): Response
    {
        try {
            $this->accountPermissionService->checkAccountCreationPermissions();
        } catch (MaxAccountsReachedException $maxAccountsReachedException) {
            $this->addFlash('error', $maxAccountsReachedException->getMessage());

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

    #[Route('/account/{accountId}/edit', name: 'account_edit')]
    public function editAccount(int $accountId, Request $request): Response
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
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
    #[Route('/account/{accountId}/delete', name: 'account_delete')]
    public function deleteAccount(int $accountId): Response
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        $this->accountListWalletManager->deleteAccount($account);

        return $this->redirectToRoute('account_list');
    }

    #[Route('/account/{accountId}/delete/{year}', name: 'account_year_delete')]
    public function deleteYearAccount(int $accountId, int $year): RedirectResponse
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
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

    #[Route('/account/{accountId}/wallet/new', name: 'account_new_wallet')]
    public function newWalletAccount(Request $request, int $accountId): Response
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
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

        return $this->render('accountList/wallet/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/account/{accountId}/wallet/new/{yearId}/{monthId}', name: 'account_wallet_add_quick_month')]
    public function newWalletForYearNextMonth(int $accountId, int $yearId, int $monthId, Request $request): Response
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        $wallet = $this->accountListWalletCreationManager->beginWalletYearCreationWithMonth($account, $yearId, $monthId);

        $form = $this->createForm(WalletCreateWithPreselectedMonthType::class, $wallet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->accountListWalletCreationManager->endWalletCreation($wallet);

            return $this->redirectToRoute('account_list');
        }

        return $this->render('account/accountList/wallet/new_quick.html.twig', [
            'form' => $form,
            'wallet' => $wallet,
        ]);
    }

    #[Route('/list/account/{accountId}/wallet/{walletId}/delete/{year}/{month}/{redirectTo?}', name: 'account_list_wallet_delete_month')]
    public function deleteWalletAndRelations(int $accountId, int $walletId, int $year, int $month): RedirectResponse
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        $wallet = $this->entityAccessService->getWalletWithAccessCheck($walletId);
        if (!$wallet instanceof Wallet) {
            return $this->redirectToRoute('account_list');
        }

        $accountId = $account->getId();
        if (null === $accountId) {
            throw new NotFoundResourceException('Account ID cannot be null');
        }

        try {
            $this->accountWalletManager->deleteWalletForMonth($accountId, $year, $month);
            $this->addFlash('success', sprintf('Wallet for %s %d deleted successfully.', MonthEnum::from($month)->getName(), $year));
        } catch (Exception $exception) {
            $this->addFlash('error', sprintf('An error occurred while deleting the wallet: %s', $exception->getMessage()));

            return $this->redirectToRoute('account_list');
        }

        return $this->redirectToRoute('account_list');
    }
}
