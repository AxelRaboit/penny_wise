<?php

declare(strict_types=1);

namespace App\Controller\Account\Wallet;

use App\Entity\Account;
use App\Entity\User;
use App\Entity\Wallet;
use App\Enum\Transaction\TransactionCategoryEnum;
use App\Enum\Wallet\MonthEnum;
use App\Form\Wallet\WalletCreateForYearType;
use App\Form\Wallet\WalletUpdateType;
use App\Manager\Refacto\Account\Wallet\AccountWalletCreationManager;
use App\Manager\Refacto\Account\Wallet\AccountWalletManager;
use App\Repository\Note\NoteRepository;
use App\Repository\Wallet\WalletRepository;
use App\Security\Voter\Account\AccountVoter;
use App\Service\Account\Wallet\Transaction\WalletTransactionService;
use App\Service\Account\Wallet\WalletChartService;
use App\Service\Account\Wallet\WalletService;
use App\Service\Checker\Account\AccountCheckerService;
use App\Service\Checker\Wallet\WalletCheckerService;
use App\Service\EntityAccessService;
use App\Service\User\UserCheckerService;
use App\Util\WalletHelper;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Model\Chart;

final class WalletAccountController extends AbstractController
{
    public function __construct(
        private readonly WalletTransactionService $walletTransactionService,
        private readonly WalletService $walletService,
        private readonly WalletRepository $walletRepository,
        private readonly NoteRepository $noteRepository,
        private readonly WalletHelper $walletHelper,
        private readonly WalletChartService $walletChartService,
        private readonly UserCheckerService $userCheckerService,
        private readonly WalletCheckerService $walletCheckerService,
        private readonly AccountCheckerService $accountCheckerService,
        private readonly AccountWalletManager $accountWalletManager,
        private readonly AccountWalletCreationManager $accountWalletCreationManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly EntityAccessService $entityAccessService,
    ) {}

    #[Route('/account/{accountId}/wallet/{walletId}/dashboard/{year}/{month}', name: 'account_wallet_dashboard')]
    public function accountWalletDashboard(int $accountId, int $walletId, int $year, int $month): Response
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        $wallet = $this->entityAccessService->getWalletWithAccessCheck($walletId);
        if (!$wallet instanceof Wallet) {
            return $this->redirectToRoute('account_list');
        }

        $wallet = $this->walletCheckerService->getWalletOrThrow($accountId, $year, $month);
        $walletsAndTransactionsFromYear = $this->walletService->getWalletsByAccountAndYear($accountId, $year);
        $transactions = $this->walletTransactionService->getAllTransactionInformationByUser($wallet);
        $notesFromWallet = $this->noteRepository->getNotesFromWallet($wallet);
        $leftToSpendChart = $this->walletChartService->createLeftToSpendChart($transactions);

        return $this->render('account/wallet/dashboard.html.twig', [
            'leftToSpendChart' => $leftToSpendChart,
            'totalSpendingForCurrentAndPreviousNthMonthsChart' => $this->walletChartService->createTotalSpendingForCurrentAndPreviousNthMonthsChart(
                $accountId,
                $year,
                $month,
                12
            ),
            'totalSpendingYearsChart' => $this->walletChartService->createTotalSpendingForCurrentAndAdjacentYearsChart($accountId),
            'wallet' => $wallet,
            'notesFromWallet' => $notesFromWallet,
            'walletsAndTransactionsFromYear' => $walletsAndTransactionsFromYear,
            'transactionCategories' => $transactions->getTransactionCategories(),
            'totalIncomesAndStartingBalance' => $transactions->getTotalIncomesAndStartingBalance(),
            'totalIncomes' => $transactions->getTotalIncomes(),
            'totalBills' => $transactions->getTotalBills(),
            'totalExpenses' => $transactions->getTotalExpenses(),
            'totalDebts' => $transactions->getTotalDebts(),
            'totalLeftToSpend' => $transactions->getTotalLeftToSpend(),
            'totalSpending' => $transactions->getTotalSpending(),
            'totalSaving' => $transactions->getTotalSaving(),
            'totalBudget' => $transactions->getTotalBudget(),
            'currentYear' => $year,
            'currentMonth' => $month,
            'account' => $account,
        ]);
    }

    #[Route('/account/{accountId}/wallet/new/year/{year}', name: 'account_wallet_new')]
    public function newWalletForYear(int $year, int $accountId, Request $request): Response
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        $wallet = $this->accountWalletCreationManager->beginWalletYearCreation($accountId, $year);

        $form = $this->createForm(WalletCreateForYearType::class, $wallet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->accountWalletCreationManager->endWalletCreation($wallet);

            return $this->redirectToRoute('account_wallet_dashboard', [
                'accountId' => $wallet->getAccount()->getId(),
                'year' => $wallet->getYear(),
                'month' => $wallet->getMonth(),
            ]);
        }

        return $this->render('wallet/walletForAccount/new_wallet_for_year.html.twig', [
            'form' => $form,
            'wallet' => $wallet,
        ]);
    }

    #[Route('/account/{accountId}/wallet/edit/{id}', name: 'account_wallet_edit', methods: ['GET', 'POST'])]
    public function editWallet(int $accountId, Wallet $wallet, Request $request): Response
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        $walletId = $wallet->getId();
        if (null === $walletId) {
            throw new LogicException('Wallet ID cannot be null');
        }

        $wallet = $this->entityAccessService->getWalletWithAccessCheck($walletId);
        if (!$wallet instanceof Wallet) {
            return $this->redirectToRoute('account_list');
        }

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

    #[Route('/account/{accountId}/wallet/copy-bills/{year}/{month}', name: 'account_wallet_copy_previous_month_bills')]
    public function copyPreviousMonthBills(int $accountId, int $year, int $month): Response
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        return $this->copyPreviousMonthTransactions(
            $accountId,
            $year,
            $month,
            TransactionCategoryEnum::Bills,
            'Bills copied successfully from the previous month.'
        );
    }

    #[Route('/account/{accountId}/wallet/copy-incomes/{year}/{month}', name: 'account_wallet_copy_previous_month_incomes')]
    public function copyPreviousMonthIncomes(int $accountId, int $year, int $month): Response
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        return $this->copyPreviousMonthTransactions(
            $accountId,
            $year,
            $month,
            TransactionCategoryEnum::Incomes,
            'Incomes copied successfully from the previous month.'
        );
    }

    #[Route('/account/{accountId}/wallet/copy-debts/{year}/{month}', name: 'account_wallet_copy_previous_month_debts')]
    public function copyPreviousMonthDebts(int $accountId, int $year, int $month): Response
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        return $this->copyPreviousMonthTransactions(
            $accountId,
            $year,
            $month,
            TransactionCategoryEnum::Debts,
            'Debts copied successfully from the previous month.'
        );
    }

    #[Route('/account/{accountId}/wallet/copy-expenses/{year}/{month}', name: 'account_wallet_copy_previous_month_expenses')]
    public function copyPreviousMonthExpenses(int $accountId, int $year, int $month): Response
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        return $this->copyPreviousMonthTransactions(
            $accountId,
            $year,
            $month,
            TransactionCategoryEnum::Expenses,
            'Expenses copied successfully from the previous month.'
        );
    }

    private function copyPreviousMonthTransactions(int $accountId, int $year, int $month, TransactionCategoryEnum $category, string $successMessage): Response
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        $account = $this->accountCheckerService->getAccountOrThrow($accountId);
        if (!$this->isGranted(AccountVoter::ACCESS_ACCOUNT, $account)) {
            return $this->redirectToRoute('account_list');
        }

        $wallet = $this->walletCheckerService->getWalletOrThrow($accountId, $year, $month);

        try {
            $this->walletTransactionService->copyTransactionsFromPreviousMonth($wallet, $category);
            $this->addFlash('success', $successMessage);
        } catch (Exception $exception) {
            $this->addFlash('warning', $exception->getMessage());
        }

        return $this->redirectToRoute('account_wallet_dashboard', [
            'accountId' => $wallet->getAccount()->getId(),
            'year' => $year,
            'month' => $month,
        ]);
    }

    #[Route('/account/{accountId}/wallet/create-previous/{year}/{month}', name: 'account_wallet_create_previous_month')]
    public function createPreviousMonthWallet(int $accountId, int $year, int $month): Response
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        return $this->createAdjacentMonthWallet($accountId, $year, $month, 'previous');
    }

    #[Route('/account/{accountId}/wallet/create-next/{year}/{month}', name: 'account_wallet_create_next_month')]
    public function createNextMonthWallet(int $accountId, int $year, int $month): Response
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        return $this->createAdjacentMonthWallet($accountId, $year, $month, 'next');
    }

    private function createAdjacentMonthWallet(int $accountId, int $year, int $month, string $direction): Response
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        try {
            $adjacentMonth = 'next' === $direction ?
                $this->walletHelper->getNextMonthAndYear($year, $month) :
                $this->walletHelper->getImmediatePreviousMonthAndYear($year, $month);

            $adjacentYear = $adjacentMonth['year'];
            $adjacentMonthEnum = MonthEnum::from($adjacentMonth['month']);

            $existingWallet = $this->walletService->getWalletByAccountYearAndMonth($accountId, $adjacentYear, $adjacentMonthEnum->value);
            if ($existingWallet instanceof Wallet) {
                $this->addFlash(
                    'warning',
                    sprintf('Wallet already exists for %s %d.', $adjacentMonthEnum->getName(), $adjacentYear)
                );

                return $this->redirectToRoute('account_wallet_dashboard', [
                    'accountId' => $accountId,
                    'year' => $adjacentYear,
                    'month' => $adjacentMonthEnum->value,
                ]);
            }

            $wallet = $this->walletCheckerService->getWalletOrThrow($accountId, $year, $month);
            $user = $wallet->getIndividual();

            $this->accountWalletManager->createWalletForMonth($user, $adjacentYear, $adjacentMonthEnum, $wallet, $account);

            $this->addFlash('success', sprintf('Wallet for %s %d created successfully.', $adjacentMonthEnum->getName(), $adjacentYear));
        } catch (Exception $exception) {
            $this->addFlash('error', sprintf('An error occurred while creating the wallet: %s', $exception->getMessage()));

            return $this->redirectToRoute('account_wallet_dashboard', [
                'accountId' => $accountId,
                'year' => $year,
                'month' => $month,
            ]);
        }

        return $this->redirectToRoute('account_wallet_dashboard', [
            'accountId' => $accountId,
            'year' => $adjacentYear,
            'month' => $adjacentMonthEnum->value,
        ]);
    }

    #[Route('/account/{accountId}/wallet/{walletId}/delete/{year}/{month}/{redirectTo?}', name: 'account_wallet_delete_month')]
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

        $user = $account->getIndividual();
        $accountId = $account->getId();
        if (null === $accountId) {
            throw new LogicException('Account ID cannot be null');
        }

        $previousMonth = $this->walletHelper->getImmediatePreviousMonthAndYear($year, $month);
        $nextMonth = $this->walletHelper->getNextMonthAndYear($year, $month);

        try {
            $this->accountWalletManager->deleteWalletForMonth($accountId, $year, $month);
            $this->addFlash('success', sprintf('Wallet for %s %d deleted successfully.', MonthEnum::from($month)->getName(), $year));
        } catch (Exception $exception) {
            $this->addFlash('error', sprintf('An error occurred while deleting the wallet: %s', $exception->getMessage()));

            return $this->redirectToRoute('account_wallet_dashboard', [
                'walletId' => $walletId,
                'accountId' => $accountId,
                'year' => $year,
                'month' => $month,
            ]);
        }

        return $this->redirectToNextAvailableWallet($accountId, [
            'year' => (int)$previousMonth['year'],
            'month' => (int)$previousMonth['month'],
        ], [
            'year' => (int)$nextMonth['year'],
            'month' => (int)$nextMonth['month'],
        ]);
    }

    /**
     * @param int $accountId
     * @param array{year: int, month: int} $previousMonth
     * @param array{year: int, month: int} $nextMonth
     * @return RedirectResponse
     */
    private function redirectToNextAvailableWallet(int $accountId, array $previousMonth, array $nextMonth): RedirectResponse
    {
        $previousWallet = $this->walletRepository->findWalletByAccountYearAndMonth($accountId, $previousMonth['year'], $previousMonth['month']);
        if ($previousWallet instanceof Wallet) {
            return $this->redirectToRoute('account_wallet_dashboard', [
                'accountId' => $previousWallet->getAccount()->getId(),
                'walletId' => $previousWallet->getId(),
                'year' => $previousMonth['year'],
                'month' => $previousMonth['month'],
            ]);
        }

        $nextWallet = $this->walletRepository->findWalletByAccountYearAndMonth($accountId, $nextMonth['year'], $nextMonth['month']);
        if ($nextWallet instanceof Wallet) {
            return $this->redirectToRoute('account_wallet_dashboard', [
                'accountId' => $nextWallet->getAccount()->getId(),
                'walletId' => $nextWallet->getId(),
                'year' => $nextMonth['year'],
                'month' => $nextMonth['month'],
            ]);
        }

        return $this->redirectToRoute('account_list');
    }

    #[Route('/account/{accountId}/wallet/reset-balance/{year}/{month}', name: 'account_wallet_reset_balance')]
    public function resetStartBalance(int $accountId, int $year, int $month): RedirectResponse
    {
        $account = $this->accountCheckerService->getAccountOrThrow($accountId);
        if (!$this->isGranted(AccountVoter::ACCESS_ACCOUNT, $account)) {
            return $this->redirectToRoute('account_list');
        }

        $user = $account->getIndividual();

        try {
            $this->accountWalletManager->resetBalance($user, $year, $month);
            $this->addFlash('success', 'Starting balance reset successfully.');
        } catch (Exception $exception) {
            $this->addFlash('warning', sprintf('An error occurred while resetting the starting balance: %s', $exception->getMessage()));
        }

        return $this->redirectToRoute('account_wallet_dashboard', [
            'accountId' => $accountId,
            'year' => $year,
            'month' => $month,
        ]);
    }

    #[Route('/account/{accountId}/wallet/copy-left-to-spend/{year}/{month}', name: 'account_wallet_copy_left_to_spend')]
    public function copyLeftToSpend(int $accountId, int $year, int $month): RedirectResponse
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_list');
        }

        $wallet = $this->walletCheckerService->getWalletOrThrow($accountId, $year, $month);

        try {
            $this->walletTransactionService->copyLeftToSpendFromPreviousMonth($wallet);
            $this->addFlash('success', sprintf('Left to spend from previous month copied successfully for %s %d.', MonthEnum::from($month)->getName(), $year));
        } catch (Exception $exception) {
            $this->addFlash('error', sprintf('An error occurred while copying left to spend from previous month: %s', $exception->getMessage()));

            return $this->redirectToRoute('account_wallet_dashboard', [
                'accountId' => $wallet->getAccount()->getId(),
                'year' => $year,
                'month' => $month,
            ]);
        }

        return $this->redirectToRoute('account_wallet_dashboard', [
            'accountId' => $wallet->getAccount()->getId(),
            'year' => $year,
            'month' => $month,
        ]);
    }

    #[Route('/account/{accountId}/wallet/chart/data', name: 'account_wallet_chart_data', methods: ['GET'])]
    public function getChartData(Request $request, int $accountId): JsonResponse
    {
        $account = $this->entityAccessService->getAccountWithAccessCheck($accountId);
        if (!$account instanceof Account) {
            return new JsonResponse(['error' => 'Account not found or not accessible.']);
        }

        $chartType = $request->query->get('type');
        $year = (int) $request->query->get('year', date('Y'));
        $month = (int) $request->query->get('month', date('n'));
        $chartFormat = (string) $request->query->get('format', Chart::TYPE_BAR);

        try {
            $chart = match ($chartType) {
                'monthly' => $this->walletChartService->createTotalSpendingForCurrentAndPreviousNthMonthsChart(
                    $accountId,
                    $year,
                    $month,
                    12,
                    $chartFormat
                ),
                'yearly' => $this->walletChartService->createTotalSpendingForCurrentAndAdjacentYearsChart(
                    $accountId,
                    $chartFormat
                ),
                default => throw new InvalidArgumentException('Invalid chart type'),
            };

            $chartHtml = $this->renderView('account/wallet/chart/chart.html.twig', [
                'chart' => $chart,
            ]);

            return new JsonResponse(['chartHtml' => $chartHtml]);
        } catch (Exception $exception) {
            return new JsonResponse(['error' => sprintf('Failed to generate chart: %s', $exception->getMessage())]);
        }
    }
}
