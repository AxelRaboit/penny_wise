<?php

declare(strict_types=1);

namespace App\Controller\Wallet;

use App\Entity\Account;
use App\Entity\User;
use App\Entity\Wallet;
use App\Enum\Transaction\TransactionCategoryEnum;
use App\Enum\Wallet\MonthEnum;
use App\Form\Wallet\WalletCreateForYearType;
use App\Form\Wallet\WalletForAccountType;
use App\Form\Wallet\WalletUpdateType;
use App\Manager\Wallet\WalletManager;
use App\Repository\Account\AccountRepository;
use App\Repository\Note\NoteRepository;
use App\Repository\Wallet\WalletRepository;
use App\Service\TransactionService;
use App\Service\WalletChartService;
use App\Service\WalletService;
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

final class WalletForAccountController extends AbstractController
{
    public function __construct(
        private readonly TransactionService $transactionService,
        private readonly WalletService $walletService,
        private readonly EntityManagerInterface $entityManager,
        private readonly WalletRepository $walletRepository,
        private readonly NoteRepository $noteRepository,
        private readonly WalletManager $walletManager,
        private readonly WalletHelper $walletHelper,
        private readonly WalletChartService $walletChartService,
        private readonly AccountRepository $accountRepository,
    ) {}

    #[Route('/account/{accountId}/wallet/new/{year}', name: 'wallet_new_for_year')]
    public function newWalletForYear(int $year, int $accountId, Request $request): Response
    {
        $account = $this->accountRepository->find($accountId);
        if (null === $account) {
            throw $this->createNotFoundException('Account not found');
        }

        $wallet = (new Wallet())
            ->setYear($year)
            ->setAccount($account);

        $form = $this->createForm(WalletCreateForYearType::class, $wallet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $wallet->setIndividual($this->getUserOrThrow());
            $this->entityManager->persist($wallet);
            $this->entityManager->flush();

            return $this->redirectToRoute('monthly_wallet', [
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

    #[Route('/account/{accountId}/wallet/edit/{id}', name: 'wallet_edit', methods: ['GET', 'POST'])]
    public function editWallet(Wallet $wallet, Request $request): Response
    {
        $form = $this->createForm(WalletUpdateType::class, $wallet);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('monthly_wallet', [
                'accountId' => $wallet->getAccount()->getId(),
                'year' => $wallet->getYear(),
                'month' => $wallet->getMonth(),
            ]);
        }

        return $this->render('wallet/walletForAccount/edit_wallet.html.twig', [
            'form' => $form,
            'wallet' => $wallet,
        ]);
    }

    #[Route('/account/{accountId}/wallet/{year}/{month}', name: 'monthly_wallet')]
    public function monthlyWallet(int $accountId, int $year, int $month): Response
    {
        $user = $this->getUserOrThrow();
        $account = $this->getAccountOrThrow($accountId, $user);
        if (null === $account->getId()) {
            throw $this->createNotFoundException('Account ID cannot be null');
        }

        $wallet = $this->getWalletOrFail($accountId, $year, $month);

        $walletsAndTransactionsFromYear = $this->walletService->getWalletsByAccountAndYear($accountId, $year);
        $transactions = $this->transactionService->getAllTransactionInformationByUser($wallet);
        $notesFromWallet = $this->noteRepository->getNotesFromWallet($wallet);
        $leftToSpendChart = $this->walletChartService->createLeftToSpendChart($transactions);

        $totalSpendingForCurrentAndPreviousNthMonthsChart = $this->walletChartService->createTotalSpendingForCurrentAndPreviousNthMonthsChart(
            $account->getId(),
            $year,
            $month,
            12
        );

        $totalSpendingYearsChart = $this->walletChartService->createTotalSpendingForCurrentAndAdjacentYearsChart($account->getId());

        return $this->render('wallet/walletForAccount/monthly.html.twig', [
            'leftToSpendChart' => $leftToSpendChart,
            'totalSpendingForCurrentAndPreviousNthMonthsChart' => $totalSpendingForCurrentAndPreviousNthMonthsChart,
            'totalSpendingYearsChart' => $totalSpendingYearsChart,
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

    #[Route('/account/{accountId}/wallet/{year}/{month}/copy-bills', name: 'copy_previous_month_bills')]
    public function copyPreviousMonthBills(int $accountId, int $year, int $month): Response
    {
        $user = $this->getUserOrThrow();
        $account = $this->getAccountOrThrow($accountId, $user);
        $accountId = $this->getAccountIdOrThrow($account);
        $currentWallet = $this->getWalletOrFail($accountId, $year, $month);

        try {
            $this->transactionService->copyTransactionsFromPreviousMonth($currentWallet, TransactionCategoryEnum::Bills);
            $this->addFlash('success', 'Bills copied successfully from the previous month.');
        } catch (Exception $exception) {
            $this->addFlash('warning', $exception->getMessage());
        }

        return $this->redirectToRoute('monthly_wallet', [
            'accountId' => $currentWallet->getAccount()->getId(),
            'year' => $year,
            'month' => $month,
        ]);
    }

    #[Route('/account/{accountId}/wallet/{year}/{month}/copy-incomes', name: 'copy_previous_month_incomes')]
    public function copyPreviousMonthIncomes(int $accountId, int $year, int $month): Response
    {
        $user = $this->getUserOrThrow();
        $account = $this->getAccountOrThrow($accountId, $user);
        $accountId = $this->getAccountIdOrThrow($account);
        $currentWallet = $this->getWalletOrFail($accountId, $year, $month);

        try {
            $this->transactionService->copyTransactionsFromPreviousMonth($currentWallet, TransactionCategoryEnum::Incomes);
            $this->addFlash('success', 'Incomes copied successfully from the previous month.');
        } catch (Exception $exception) {
            $this->addFlash('warning', $exception->getMessage());
        }

        return $this->redirectToRoute('monthly_wallet', [
            'accountId' => $currentWallet->getAccount()->getId(),
            'year' => $year,
            'month' => $month,
        ]);
    }

    #[Route('/account/{accountId}/wallet/{year}/{month}/copy-debts', name: 'copy_previous_month_debts')]
    public function copyPreviousMonthDebts(int $accountId, int $year, int $month): Response
    {
        $user = $this->getUserOrThrow();
        $account = $this->getAccountOrThrow($accountId, $user);
        $accountId = $this->getAccountIdOrThrow($account);
        $currentWallet = $this->getWalletOrFail($accountId, $year, $month);

        try {
            $this->transactionService->copyTransactionsFromPreviousMonth($currentWallet, TransactionCategoryEnum::Debts);
            $this->addFlash('success', 'Debts copied successfully from the previous month.');
        } catch (Exception $exception) {
            $this->addFlash('warning', $exception->getMessage());
        }

        return $this->redirectToRoute('monthly_wallet', [
            'accountId' => $currentWallet->getAccount()->getId(),
            'year' => $year,
            'month' => $month,
        ]);
    }

    #[Route('/account/{accountId}/wallet/{year}/{month}/copy-expenses', name: 'copy_previous_month_expenses')]
    public function copyPreviousMonthExpenses(int $accountId, int $year, int $month): Response
    {
        $user = $this->getUserOrThrow();
        $account = $this->getAccountOrThrow($accountId, $user);
        $accountId = $this->getAccountIdOrThrow($account);
        $currentWallet = $this->getWalletOrFail($accountId, $year, $month);

        try {
            $this->transactionService->copyTransactionsFromPreviousMonth($currentWallet, TransactionCategoryEnum::Expenses);
            $this->addFlash('success', 'Expenses copied successfully from the previous month.');
        } catch (Exception $exception) {
            $this->addFlash('warning', $exception->getMessage());
        }

        return $this->redirectToRoute('monthly_wallet', [
            'accountId' => $currentWallet->getAccount()->getId(),
            'year' => $year,
            'month' => $month,
        ]);
    }

    #[Route('/account/{accountId}/wallet/new', name: 'wallet_new_for_account')]
    public function newWalletForAccount(Request $request, int $accountId): Response
    {
        $user = $this->getUserOrThrow();
        $account = $this->getAccountOrThrow($accountId, $user);

        $wallet = new Wallet();
        $wallet->setAccount($account);

        $form = $this->createForm(WalletForAccountType::class, $wallet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUserOrThrow();
            $wallet->setIndividual($user);

            if (!$wallet->getAccount() instanceof Account) {
                throw new LogicException('Wallet must be linked to an account before persisting.');
            }

            $this->entityManager->persist($wallet);
            $this->entityManager->flush();

            return $this->redirectToRoute('monthly_wallet', [
                'accountId' => $wallet->getAccount()->getId(),
                'year' => $wallet->getYear(),
                'month' => $wallet->getMonth(),
            ]);
        }

        return $this->render('wallet/walletForAccount/new_for_account.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/account/{accountId}/wallet/create-next/{year}/{month}', name: 'create_next_month_wallet')]
    public function createNextMonthWallet(int $accountId, int $year, int $month): Response
    {
        $user = $this->getUserOrThrow();

        try {
            $account = $this->getAccountOrThrow($accountId, $user);
            $nextMonth = $this->walletHelper->getNextMonthAndYear($year, $month);
            $nextYear = $nextMonth['year'];
            $nextMonthEnum = MonthEnum::from($nextMonth['month']);
            $existingWallet = $this->walletService->getWalletByAccountYearAndMonth($accountId, $nextYear, $nextMonthEnum->value);
            if ($existingWallet instanceof Wallet) {
                $this->addFlash(
                    'warning',
                    sprintf('Wallet already exists for %s %d.', $nextMonthEnum->getName(), $nextYear)
                );

                return $this->redirectToRoute('monthly_wallet', [
                    'accountId' => $accountId,
                    'year' => $nextYear,
                    'month' => $nextMonthEnum->value,
                ]);
            }

            $wallet = $this->getWalletOrFail($accountId, $year, $month);

            $this->walletManager->createWalletForMonth($user, $nextYear, $nextMonthEnum, $wallet, $account);

            $this->addFlash('success', sprintf('Wallet for %s %d created successfully.', $nextMonthEnum->getName(), $nextYear));
        } catch (Exception $exception) {
            $this->addFlash('error', sprintf('An error occurred while creating the wallet: %s', $exception->getMessage()));

            return $this->redirectToRoute('monthly_wallet', [
                'accountId' => $accountId,
                'year' => $year,
                'month' => $month,
            ]);
        }

        return $this->redirectToRoute('monthly_wallet', [
            'accountId' => $accountId,
            'year' => $nextYear,
            'month' => $nextMonthEnum->value,
        ]);
    }

    #[Route('/account/{accountId}/wallet/create-previous/{year}/{month}', name: 'create_previous_month_wallet')]
    public function createPreviousMonthWallet(int $accountId, int $year, int $month): Response
    {
        $user = $this->getUserOrThrow();

        try {
            $account = $this->accountRepository->find($accountId);
            if (!$account || $account->getIndividual() !== $user) {
                throw $this->createNotFoundException('Account not found or does not belong to user');
            }

            $previousMonth = $this->walletHelper->getImmediatePreviousMonthAndYear($year, $month);
            $previousYear = $previousMonth['year'];
            $previousMonthEnum = MonthEnum::from($previousMonth['month']);

            $existingWallet = $this->walletService->getWalletByAccountYearAndMonth($accountId, $previousYear, $previousMonthEnum->value);
            if ($existingWallet instanceof Wallet) {
                $this->addFlash(
                    'warning',
                    sprintf('Wallet already exists for %s %d.', $previousMonthEnum->getName(), $previousYear)
                );

                return $this->redirectToRoute('monthly_wallet', [
                    'accountId' => $accountId,
                    'year' => $previousYear,
                    'month' => $previousMonthEnum->value,
                ]);
            }

            $wallet = $this->getWalletOrFail($accountId, $year, $month);

            $this->walletManager->createWalletForMonth($user, $previousYear, $previousMonthEnum, $wallet, $account);

            $this->addFlash('success', sprintf('Wallet for %s %d created successfully.', $previousMonthEnum->getName(), $previousYear));
        } catch (Exception $exception) {
            $this->addFlash('error', sprintf('An error occurred while creating the wallet: %s', $exception->getMessage()));

            return $this->redirectToRoute('monthly_wallet', [
                'accountId' => $accountId,
                'year' => $year,
                'month' => $month,
            ]);
        }

        return $this->redirectToRoute('monthly_wallet', [
            'accountId' => $accountId,
            'year' => $previousYear,
            'month' => $previousMonthEnum->value,
        ]);
    }

    #[Route('/wallet/delete/{year}/{month}/{redirectTo?}', name: 'delete_monthly_wallet')]
    public function deleteWalletAndRelations(int $year, int $month, ?string $redirectTo = null): RedirectResponse
    {
        $user = $this->getUserOrThrow();

        $wallet = $this->walletRepository->findWalletByUser($user, $year, $month);
        if (!$wallet instanceof Wallet) {
            throw $this->createNotFoundException('Wallet not found for the given year and month.');
        }

        $accountId = $wallet->getAccount()->getId();

        $previousMonth = $this->walletHelper->getImmediatePreviousMonthAndYear($year, $month);
        $nextMonth = $this->walletHelper->getNextMonthAndYear($year, $month);

        try {
            $this->walletManager->deleteWalletForMonth($user, $year, $month);
            $this->addFlash('success', sprintf('Wallet for %s %d deleted successfully.', MonthEnum::from($month)->getName(), $year));
        } catch (Exception $exception) {
            $this->addFlash('error', sprintf('An error occurred while deleting the wallet: %s', $exception->getMessage()));

            return $this->redirectToRoute('monthly_wallet', [
                'accountId' => $accountId,
                'year' => $year,
                'month' => $month,
            ]);
        }

        if ('account_list' === $redirectTo) {
            return $this->redirectToRoute('account_list');
        }

        $previousWallet = $this->walletRepository->findWalletByUser($user, $previousMonth['year'], $previousMonth['month']);
        if ($previousWallet instanceof Wallet) {
            return $this->redirectToRoute('monthly_wallet', [
                'accountId' => $previousWallet->getAccount()->getId(),
                'year' => $previousMonth['year'],
                'month' => $previousMonth['month'],
            ]);
        }

        $nextWallet = $this->walletRepository->findWalletByUser($user, $nextMonth['year'], $nextMonth['month']);
        if ($nextWallet instanceof Wallet) {
            return $this->redirectToRoute('monthly_wallet', [
                'accountId' => $nextWallet->getAccount()->getId(),
                'year' => $nextMonth['year'],
                'month' => $nextMonth['month'],
            ]);
        }

        return $this->redirectToRoute('account_list');
    }

    #[Route('/account/{accountId}/wallet/reset-start-balance/{year}/{month}', name: 'reset_start_balance')]
    public function resetStartBalance(int $accountId, int $year, int $month): RedirectResponse
    {
        $user = $this->getUserOrThrow();

        try {
            $this->walletManager->resetStartBalanceForMonth($user, $year, $month);
            $this->addFlash('success', 'Starting balance reset successfully.');
        } catch (Exception $exception) {
            $this->addFlash('warning', sprintf('An error occurred while resetting the starting balance: %s', $exception->getMessage()));
        }

        return $this->redirectToRoute('monthly_wallet', [
            'accountId' => $accountId,
            'year' => $year,
            'month' => $month,
        ]);
    }

    #[Route('/account/{accountId}/wallet/copy-left-to-spend/{year}/{month}', name: 'copy_left_to_spend')]
    public function copyLeftToSpend(int $accountId, int $year, int $month): RedirectResponse
    {
        $wallet = $this->getWalletOrFail($accountId, $year, $month);

        try {
            $this->transactionService->copyLeftToSpendFromPreviousMonth($wallet);
            $this->addFlash('success', sprintf('Left to spend from previous month copied successfully for %s %d.', MonthEnum::from($month)->getName(), $year));
        } catch (Exception $exception) {
            $this->addFlash('error', sprintf('An error occurred while copying left to spend from previous month: %s', $exception->getMessage()));

            return $this->redirectToRoute('monthly_wallet', [
                'accountId' => $wallet->getAccount()->getId(),
                'year' => $year,
                'month' => $month,
            ]);
        }

        return $this->redirectToRoute('monthly_wallet', [
            'accountId' => $wallet->getAccount()->getId(),
            'year' => $year,
            'month' => $month,
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

    private function getAccountOrThrow(int $accountId, User $user): Account
    {
        $account = $this->accountRepository->find($accountId);
        if (!$account || $account->getIndividual() !== $user) {
            throw $this->createNotFoundException('Account not found or does not belong to user');
        }

        return $account;
    }

    private function getAccountIdOrThrow(Account $account): int
    {
        $accountId = $account->getId();
        if (null === $accountId) {
            throw $this->createNotFoundException('Account ID cannot be null');
        }

        return $accountId;
    }

    private function getWalletOrFail(int $accountId, int $year, int $month): Wallet
    {
        $wallet = $this->walletService->getWalletByAccountYearAndMonth($accountId, $year, $month);
        if (!$wallet instanceof Wallet) {
            throw $this->createNotFoundException('Wallet not found');
        }

        return $wallet;
    }

    #[Route('/api/get-chart-data/{accountId}', name: 'get_chart_data', methods: ['GET'])]
    public function getChartData(Request $request, int $accountId): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found');
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

            $chartHtml = $this->renderView('wallet/walletForAccount/api/chart.html.twig', [
                'chart' => $chart,
            ]);

            return new JsonResponse(['chartHtml' => $chartHtml]);
        } catch (Exception $exception) {
            return new JsonResponse(['error' => sprintf('Failed to generate chart: %s', $exception->getMessage())], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
