<?php

declare(strict_types=1);

namespace App\Controller\Account\Wallet;

use App\Entity\Account;
use App\Entity\Wallet;
use App\Enum\Transaction\TransactionCategoryEnum;
use App\Enum\Wallet\MonthEnum;
use App\Form\Wallet\WalletCreateForYearType;
use App\Form\Wallet\WalletUpdateType;
use App\Manager\Account\Wallet\AccountWalletCreationManager;
use App\Manager\Account\Wallet\AccountWalletManager;
use App\Repository\Note\NoteRepository;
use App\Repository\Wallet\WalletRepository;
use App\Service\Account\Wallet\Transaction\WalletTransactionService;
use App\Service\Account\Wallet\WalletChartService;
use App\Service\Account\Wallet\WalletService;
use App\Util\WalletHelper;
use DateMalformedStringException;
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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
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
        private readonly AccountWalletManager $accountWalletManager,
        private readonly AccountWalletCreationManager $accountWalletCreationManager,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * @throws DateMalformedStringException
     */
    #[Route('/account/{account}/wallet/{wallet}/dashboard/{year}/{month}', name: 'account_wallet_dashboard')]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    #[IsGranted('ACCESS_WALLET', subject: 'wallet')]
    public function accountWalletDashboard(Account $account, Wallet $wallet, int $year, int $month): Response
    {
        $accountId = $account->getId();
        if (null === $accountId) {
            throw new NotFoundResourceException('Account not found');
        }

        $walletsAndTransactionsFromYear = $this->walletService->getWalletsByAccountAndYear($accountId, $year);
        $transactions = $this->walletTransactionService->getAllTransactionInformationByUser($wallet);
        $notesFromWallet = $this->noteRepository->getNotesFromWallet($wallet);
        $leftToSpendChart = $this->walletChartService->createLeftToSpendChart($transactions);
        $walletNavigation = $this->walletService->getWalletNavigationForCurrentMonth($accountId, $year, $month);

        return $this->render('account/wallet/dashboard/dashboard.html.twig', [
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
            'navigationPreviousWallet' => $walletNavigation['navigationPreviousWallet'],
            'navigationNextWallet' => $walletNavigation['navigationNextWallet'],
        ]);
    }

    #[Route('/account/{account}/wallet/{wallet}/new/year/{year}', name: 'account_wallet_new')]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    #[IsGranted('ACCESS_WALLET', subject: 'wallet')]
    public function newWalletForYear(int $year, Account $account, Wallet $wallet, Request $request): Response
    {
        $accountId = $account->getId();
        if (null === $accountId) {
            throw new NotFoundResourceException('Account not found');
        }

        // TODO AXEL: I have a doubt about having {wallet} in the url
        $wallet = $this->accountWalletCreationManager->beginWalletYearCreation($account, $year);

        $form = $this->createForm(WalletCreateForYearType::class, $wallet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->accountWalletCreationManager->endWalletCreation($wallet);

            return $this->redirectToRoute('account_wallet_dashboard', [
                'wallet' => $wallet->getId(),
                'account' => $wallet->getAccount()->getId(),
                'year' => $wallet->getYear(),
                'month' => $wallet->getMonth(),
            ]);
        }

        // TODO AXEL: Dans account list, voir pour le add button next month car le soucis c'est que d'une autre façon, il est possible de créer par exemple decembre alors que le mois d'avant était Mars, donc du coup les mois entre, impossible de les créé via ici

        return $this->render('account/wallet/dashboard/wallet/new.html.twig', [
            'form' => $form,
            'wallet' => $wallet,
        ]);
    }

    #[Route('/account/{account}/wallet/edit/{wallet}', name: 'account_wallet_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    public function editWallet(Account $account, Wallet $wallet, Request $request): Response
    {
        $form = $this->createForm(WalletUpdateType::class, $wallet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('account_wallet_dashboard', [
                'wallet' => $wallet->getId(),
                'account' => $wallet->getAccount()->getId(),
                'year' => $wallet->getYear(),
                'month' => $wallet->getMonth(),
            ]);
        }

        return $this->render('account/wallet/dashboard/wallet/edit.html.twig', [
            'form' => $form,
            'wallet' => $wallet,
        ]);
    }

    #[Route('/account/{account}/wallet/{wallet}/copy-bills/{year}/{month}', name: 'account_wallet_copy_previous_month_bills')]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    public function copyPreviousMonthBills(Account $account, Wallet $wallet, int $year, int $month): Response
    {
        return $this->copyPreviousMonthTransactions(
            $wallet,
            $year,
            $month,
            TransactionCategoryEnum::Bills,
            'Bills copied successfully from the previous month.'
        );
    }

    #[Route('/account/{account}/wallet/{wallet}/copy-incomes/{year}/{month}', name: 'account_wallet_copy_previous_month_incomes')]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    #[IsGranted('ACCESS_WALLET', subject: 'wallet')]
    public function copyPreviousMonthIncomes(Account $account, Wallet $wallet, int $year, int $month): Response
    {
        return $this->copyPreviousMonthTransactions(
            $wallet,
            $year,
            $month,
            TransactionCategoryEnum::Incomes,
            'Incomes copied successfully from the previous month.'
        );
    }

    #[Route('/account/{account}/wallet/{wallet}/copy-debts/{year}/{month}', name: 'account_wallet_copy_previous_month_debts')]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    public function copyPreviousMonthDebts(Account $account, Wallet $wallet, int $year, int $month): Response
    {
        return $this->copyPreviousMonthTransactions(
            $wallet,
            $year,
            $month,
            TransactionCategoryEnum::Debts,
            'Debts copied successfully from the previous month.'
        );
    }

    #[Route('/account/{account}/wallet/{wallet}/copy-expenses/{year}/{month}', name: 'account_wallet_copy_previous_month_expenses')]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    public function copyPreviousMonthExpenses(Account $account, Wallet $wallet, int $year, int $month): Response
    {
        $accountId = $account->getId();
        if (null === $accountId) {
            throw new NotFoundResourceException('Account not found');
        }

        return $this->copyPreviousMonthTransactions(
            $wallet,
            $year,
            $month,
            TransactionCategoryEnum::Expenses,
            'Expenses copied successfully from the previous month.'
        );
    }

    #[Route('/account/{account}/wallet/{wallet}/create-previous/{year}/{month}', name: 'account_wallet_create_previous_month')]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    #[IsGranted('ACCESS_WALLET', subject: 'wallet')]
    public function createPreviousMonthWallet(Account $account, Wallet $wallet, int $year, int $month): Response
    {
        return $this->createAdjacentMonthWallet($account, $wallet, $year, $month, 'previous');
    }

    #[Route('/account/{account}/wallet/{wallet}/create-next/{year}/{month}', name: 'account_wallet_create_next_month')]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    #[IsGranted('ACCESS_WALLET', subject: 'wallet')]
    public function createNextMonthWallet(Account $account, Wallet $wallet, int $year, int $month): Response
    {
        return $this->createAdjacentMonthWallet($account, $wallet, $year, $month, 'next');
    }

    #[Route('/account/{account}/wallet/{wallet}/delete/{year}/{month}/{redirectTo?}', name: 'account_wallet_delete_month')]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    #[IsGranted('ACCESS_WALLET', subject: 'wallet')]
    public function deleteWalletAndRelations(Account $account, Wallet $wallet, int $year, int $month): RedirectResponse
    {
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
                'wallet' => $wallet->getId(),
                'account' => $account->getId(),
                'year' => $year,
                'month' => $month,
            ]);
        }

        return $this->redirectToNextAvailableWallet($accountId, [
            'year' => $previousMonth['year'],
            'month' => $previousMonth['month'],
        ], [
            'year' => $nextMonth['year'],
            'month' => $nextMonth['month'],
        ]);
    }

    #[Route('/account/{account}/wallet/{wallet}/reset-balance/{year}/{month}', name: 'account_wallet_reset_balance')]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    #[IsGranted('ACCESS_WALLET', subject: 'wallet')]
    public function resetStartBalance(Account $account, Wallet $wallet, int $year, int $month): RedirectResponse
    {
        $user = $account->getUser();

        try {
            $this->accountWalletManager->resetBalance($user, $year, $month);
            $this->addFlash('success', 'Starting balance reset successfully.');
        } catch (Exception $exception) {
            $this->addFlash('warning', sprintf('An error occurred while resetting the starting balance: %s', $exception->getMessage()));
        }

        return $this->redirectToRoute('account_wallet_dashboard', [
            'account' => $account->getId(),
            'wallet' => $wallet->getId(),
            'year' => $year,
            'month' => $month,
        ]);
    }

    #[Route('/account/{account}/wallet/{wallet}/copy-left-to-spend/{year}/{month}', name: 'account_wallet_copy_left_to_spend')]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    #[IsGranted('ACCESS_WALLET', subject: 'wallet')]
    public function copyLeftToSpend(Account $account, Wallet $wallet, int $year, int $month): RedirectResponse
    {
        try {
            $this->walletTransactionService->copyLeftToSpendFromPreviousMonth($wallet);
            $this->addFlash('success', sprintf('Left to spend from previous month copied successfully for %s %d.', MonthEnum::from($month)->getName(), $year));
        } catch (Exception $exception) {
            $this->addFlash('error', sprintf('An error occurred while copying left to spend from previous month: %s', $exception->getMessage()));

            return $this->redirectToRoute('account_wallet_dashboard', [
                'wallet' => $wallet->getId(),
                'account' => $account->getId(),
                'year' => $year,
                'month' => $month,
            ]);
        }

        return $this->redirectToRoute('account_wallet_dashboard', [
            'wallet' => $wallet->getId(),
            'account' => $wallet->getAccount()->getId(),
            'year' => $year,
            'month' => $month,
        ]);
    }

    #[Route('/account/{account}/wallet/{wallet}/chart/data', name: 'account_wallet_chart_data', methods: ['GET'])]
    #[IsGranted('ACCESS_ACCOUNT', subject: 'account')]
    #[IsGranted('ACCESS_WALLET', subject: 'wallet')]
    public function getChartData(Request $request, Account $account, Wallet $wallet): JsonResponse
    {
        $accountId = $account->getId();
        if (null === $accountId) {
            throw new NotFoundResourceException('Account not found');
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

            $chartHtml = $this->renderView('account/wallet/dashboard/chart/chart.html.twig', [
                'chart' => $chart,
            ]);

            return new JsonResponse(['chartHtml' => $chartHtml]);
        } catch (Exception $exception) {
            return new JsonResponse(['error' => sprintf('Failed to generate chart: %s', $exception->getMessage())]);
        }
    }

    // PRIVATE METHODS

    /**
     * @param array{year: int, month: int} $previousMonth
     * @param array{year: int, month: int} $nextMonth
     */
    private function redirectToNextAvailableWallet(int $accountId, array $previousMonth, array $nextMonth): RedirectResponse
    {
        $previousWallet = $this->walletRepository->findWalletByAccountYearAndMonth($accountId, $previousMonth['year'], $previousMonth['month']);
        if ($previousWallet instanceof Wallet) {
            return $this->redirectToRoute('account_wallet_dashboard', [
                'account' => $previousWallet->getAccount()->getId(),
                'wallet' => $previousWallet->getId(),
                'year' => $previousMonth['year'],
                'month' => $previousMonth['month'],
            ]);
        }

        $nextWallet = $this->walletRepository->findWalletByAccountYearAndMonth($accountId, $nextMonth['year'], $nextMonth['month']);
        if ($nextWallet instanceof Wallet) {
            return $this->redirectToRoute('account_wallet_dashboard', [
                'account' => $nextWallet->getAccount()->getId(),
                'wallet' => $nextWallet->getId(),
                'year' => $nextMonth['year'],
                'month' => $nextMonth['month'],
            ]);
        }

        return $this->redirectToRoute('account_list');
    }

    private function createAdjacentMonthWallet(Account $account, Wallet $wallet, int $year, int $month, string $direction): Response
    {
        $accountId = $account->getId();
        $walletId = $wallet->getId();
        if (null === $accountId || null === $walletId) {
            throw new LogicException('Account ID or wallet ID cannot be null');
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
                    'wallet' => $walletId,
                    'account' => $accountId,
                    'year' => $adjacentYear,
                    'month' => $adjacentMonthEnum->value,
                ]);
            }

            $user = $wallet->getUser();

            $this->accountWalletManager->createWalletForMonth($user, $adjacentYear, $adjacentMonthEnum, $wallet, $account);

            $this->addFlash('success', sprintf('Wallet for %s %d created successfully.', $adjacentMonthEnum->getName(), $adjacentYear));
        } catch (Exception $exception) {
            $this->addFlash('error', sprintf('An error occurred while creating the wallet: %s', $exception->getMessage()));

            return $this->redirectToRoute('account_wallet_dashboard', [
                'wallet' => $wallet->getId(),
                'account' => $accountId,
                'year' => $year,
                'month' => $month,
            ]);
        }

        return $this->redirectToRoute('account_wallet_dashboard', [
            'wallet' => $wallet->getId(),
            'account' => $accountId,
            'year' => $adjacentYear,
            'month' => $adjacentMonthEnum->value,
        ]);
    }

    private function copyPreviousMonthTransactions(Wallet $wallet, int $year, int $month, TransactionCategoryEnum $category, string $successMessage): Response
    {
        try {
            $this->walletTransactionService->copyTransactionsFromPreviousMonth($wallet, $category);
            $this->addFlash('success', $successMessage);
        } catch (Exception $exception) {
            $this->addFlash('warning', $exception->getMessage());
        }

        return $this->redirectToRoute('account_wallet_dashboard', [
            'wallet' => $wallet->getId(),
            'account' => $wallet->getAccount()->getId(),
            'year' => $year,
            'month' => $month,
        ]);
    }
}
