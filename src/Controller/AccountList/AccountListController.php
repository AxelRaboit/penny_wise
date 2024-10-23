<?php

declare(strict_types=1);

namespace App\Controller\AccountList;

use App\Entity\Account;
use App\Entity\Wallet;
use App\Enum\Wallet\MonthEnum;
use App\Form\Account\AccountType;
use App\Form\Account\Wallet\WalletType;
use App\Form\Wallet\WalletCreateWithPreselectedMonthType;
use App\Manager\Account\Wallet\AccountWalletManager;
use App\Manager\AccountList\AccountListWalletManager;
use App\Manager\AccountList\Wallet\AccountListWalletCreationManager;
use App\Service\Account\Wallet\WalletService;
use App\Service\AccountList\AccountListService;
use App\Service\User\UserCheckerService;
use Exception;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

final class AccountListController extends AbstractController
{
    public function __construct(
        private readonly WalletService $walletService,
        private readonly UserCheckerService $userCheckerService,
        private readonly AccountListWalletManager $accountListWalletManager,
        private readonly AccountListWalletCreationManager $accountListWalletCreationManager,
        private readonly AccountWalletManager $accountWalletManager,
        private readonly AccountListService $accountListService,
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

    /**
     * @throws RandomException
     */
    #[Route('/account/new', name: 'account_new')]
    #[IsGranted('CREATE_ACCOUNT')]
    public function newAccount(Request $request): Response
    {
        $account = $this->accountListService->beginAccountCreation();

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

    #[Route('/account/{account}/edit', name: 'account_edit')]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    public function editAccount(Account $account, Request $request): Response
    {
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
    #[Route('/account/{account}/delete', name: 'account_delete')]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    public function deleteAccount(Account $account): Response
    {
        $this->accountListWalletManager->deleteAccount($account);

        return $this->redirectToRoute('account_list');
    }

    #[Route('/account/{account}/delete/{year}', name: 'account_year_delete')]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    public function deleteYearAccount(Account $account, int $year): RedirectResponse
    {
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

    #[Route('/account/{account}/wallet/new', name: 'account_new_wallet')]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    public function newWalletAccount(Request $request, Account $account): Response
    {
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

    #[Route('/account/{account}/wallet/new/{yearId}/{monthId}', name: 'account_wallet_add_quick_month')]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    public function newWalletForYearNextMonth(Account $account, int $yearId, int $monthId, Request $request): Response
    {
        $wallet = $this->accountListWalletCreationManager->beginWalletYearCreationWithMonth($account, $yearId, $monthId);

        $form = $this->createForm(WalletCreateWithPreselectedMonthType::class, $wallet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->accountListWalletCreationManager->endWalletCreation($wallet);

            return $this->redirectToRoute('account_list');
        }

        return $this->render('accountList/wallet/new_quick.html.twig', [
            'form' => $form,
            'wallet' => $wallet,
        ]);
    }

    #[Route('/list/account/{account}/wallet/{wallet}/delete/{year}/{month}/{redirectTo?}', name: 'account_list_wallet_delete_month')]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    #[IsGranted('ACCESS_WALLET', subject: 'wallet')]
    public function deleteWalletAndRelations(Account $account, Wallet $wallet, int $year, int $month): RedirectResponse
    {
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
