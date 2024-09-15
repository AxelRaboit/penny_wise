<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Entity\Wallet;
use App\Enum\MonthEnum;
use App\Exception\NoPreviousTransactionsException;
use App\Exception\NoPreviousWalletException;
use App\Form\WalletType;
use App\Manager\WalletManager;
use App\Repository\LinkRepository;
use App\Repository\NoteRepository;
use App\Repository\WalletRepository;
use App\Service\TransactionService;
use App\Service\WalletService;
use App\Util\WalletHelper;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WalletController extends AbstractController
{
    private const string MONTHLY_WALLET_TEMPLATE = 'wallet/monthly.html.twig';

    private const string NEW_WALLET_TEMPLATE = 'wallet/new.html.twig';

    private const int BILL_CATEGORY_ID = 1;

    private const int EXPENSE_CATEGORY_ID = 2;

    public function __construct(
        private readonly TransactionService $transactionService,
        private readonly WalletService $walletService,
        private readonly EntityManagerInterface $entityManager,
        private readonly WalletRepository $walletRepository,
        private readonly NoteRepository $noteRepository,
        private readonly LinkRepository $linkRepository,
        private readonly WalletManager $walletManager,
        private readonly WalletHelper $walletHelper,
    ) {}

    /**
     * @throws Exception
     */
    #[Route('/wallet/{year}/{month}', name: 'monthly_wallet')]
    public function monthlyWallet(int $year, int $month): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found');
        }

        $wallet = $this->walletService->getWalletByUser($user, $year, $month);
        if (!$wallet instanceof Wallet) {
            throw $this->createNotFoundException('Wallet not found');
        }

        $transactions = $this->transactionService->getAllTransactionInformationByUser($wallet);
        $walletsAndTransactionsFromYear = $this->walletRepository->getAllWalletsAndTransactionsFromYear($year);
        $notesFromWallet = $this->noteRepository->getNotesFromWallet($wallet);
        $leftToSpendChart = $this->walletService->createLeftToSpendChart($transactions);
        $totalSpendingForCurrentAndPreviousNthMonthsChart = $this->walletService->createTotalSpendingForCurrentAndPreviousNthMonthsChart($year, $month, 3);
        $userLinks = $this->linkRepository->findByIndividual($user);
        $totalSpendingYearsChart = $this->walletService->createTotalSpendingForCurrentAndAdjacentYearsChart();

        $options = [
            'userLinks' => $userLinks,
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
            'currentYear' => $year,
            'currentMonth' => $month,
        ];

        return $this->render(self::MONTHLY_WALLET_TEMPLATE, $options);
    }

    /**
     * @throws Exception
     */
    #[Route('/wallet/{year}/{month}/copy-bills', name: 'copy_previous_month_bills')]
    public function copyPreviousMonthBills(int $year, int $month): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $currentWallet = $this->walletService->getWalletByUser($user, $year, $month);

        try {
            $this->transactionService->copyTransactionsFromPreviousMonth($currentWallet, self::BILL_CATEGORY_ID);
            $this->addFlash('success', 'Expenses copied successfully from the previous month.');
        } catch (NoPreviousWalletException|NoPreviousTransactionsException $exception) {
            $this->addFlash('warning', $exception->getMessage());
        }

        return $this->redirectToRoute('monthly_wallet', [
            'year' => $year,
            'month' => $month,
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/wallet/{year}/{month}/copy-expenses', name: 'copy_previous_month_expenses')]
    public function copyPreviousMonthExpenses(int $year, int $month): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $currentWallet = $this->walletService->getWalletByUser($user, $year, $month);

        try {
            $this->transactionService->copyTransactionsFromPreviousMonth($currentWallet, self::EXPENSE_CATEGORY_ID);
            $this->addFlash('success', 'Expenses copied successfully from the previous month.');
        } catch (NoPreviousWalletException|NoPreviousTransactionsException $exception) {
            $this->addFlash('warning', $exception->getMessage());
        }

        return $this->redirectToRoute('monthly_wallet', [
            'year' => $year,
            'month' => $month,
        ]);
    }

    #[Route('/wallet/new', name: 'wallet_new')]
    public function new(Request $request): Response
    {
        $wallet = new Wallet();
        $form = $this->createForm(WalletType::class, $wallet);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            $wallet->setIndividual($user);
            $this->entityManager->persist($wallet);
            $this->entityManager->flush();

            return $this->redirectToRoute('monthly_wallet', [
                'year' => $wallet->getYear(),
                'month' => $wallet->getMonth(),
            ]);
        }

        return $this->render(self::NEW_WALLET_TEMPLATE, [
            'form' => $form,
        ]);
    }

    #[Route('/wallet/create-next/{year}/{month}', name: 'create_next_month_wallet')]
    public function createNextMonthWallet(int $year, int $month): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found');
        }

        try {
            $nextMonth = $this->walletHelper->getNextMonthAndYear($year, $month);
            $nextYear = $nextMonth['year'];
            $nextMonthEnum = MonthEnum::from($nextMonth['month']);

            $existingWallet = $this->walletService->getWalletByUser($user, $nextYear, $nextMonthEnum->value);
            if ($existingWallet instanceof Wallet) {
                $this->addFlash(
                    'warning',
                    sprintf('Wallet already exists for %s %d.', $nextMonthEnum->getName(), $nextYear)
                );

                return $this->redirectToRoute('monthly_wallet', [
                    'year' => $nextYear,
                    'month' => $nextMonthEnum->value,
                ]);
            }

            $currentWallet = $this->walletService->getWalletByUser($user, $year, $month);
            if (!$currentWallet instanceof Wallet) {
                throw $this->createNotFoundException();
            }

            $this->walletManager->createWalletForMonth($user, $nextYear, $nextMonthEnum, $currentWallet);

            $this->addFlash('success', sprintf('Wallet for %s %d created successfully.', $nextMonthEnum->getName(), $nextYear));
        } catch (Exception $exception) {
            $this->addFlash('error', sprintf('An error occurred while creating the wallet: %s', $exception->getMessage()));

            return $this->redirectToRoute('monthly_wallet', ['year' => $year, 'month' => $month]);
        }

        return $this->redirectToRoute('monthly_wallet', ['year' => $nextYear, 'month' => $nextMonthEnum->value]);
    }

    #[Route('/wallet/create-previous/{year}/{month}', name: 'create_previous_month_wallet')]
    public function createPreviousMonthWallet(int $year, int $month): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found');
        }

        try {
            $previousMonth = $this->walletHelper->getPreviousMonthAndYear($year, $month);
            $previousYear = $previousMonth['year'];
            $previousMonthEnum = MonthEnum::from($previousMonth['month']);

            $existingWallet = $this->walletService->getWalletByUser($user, $previousYear, $previousMonthEnum->value);
            if ($existingWallet instanceof Wallet) {
                $this->addFlash(
                    'warning',
                    sprintf('Wallet already exists for %s %d.', $previousMonthEnum->getName(), $previousYear)
                );

                return $this->redirectToRoute('monthly_wallet', [
                    'year' => $previousYear,
                    'month' => $previousMonthEnum->value,
                ]);
            }

            $currentWallet = $this->walletService->getWalletByUser($user, $year, $month);
            if (!$currentWallet instanceof Wallet) {
                throw $this->createNotFoundException();
            }

            $this->walletManager->createWalletForMonth($user, $previousYear, $previousMonthEnum, $currentWallet);

            $this->addFlash('success', sprintf('Wallet for %s %d created successfully.', $previousMonthEnum->getName(), $previousYear));
        } catch (Exception $exception) {
            $this->addFlash('error', sprintf('An error occurred while creating the wallet: %s', $exception->getMessage()));

            return $this->redirectToRoute('monthly_wallet', ['year' => $year, 'month' => $month]);
        }

        return $this->redirectToRoute('monthly_wallet', ['year' => $previousYear, 'month' => $previousMonthEnum->value]);
    }

    #[Route('/wallet/delete/{year}/{month}', name: 'delete_monthly_wallet')]
    public function deleteMonthlyWalletAndItsTransactions(int $year, int $month): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found');
        }

        $previousMonth = $this->walletHelper->getPreviousMonthAndYear($year, $month);

        try {
            $this->walletManager->deleteWalletForMonth($user, $year, $month);
            $this->addFlash('success', sprintf('Wallet for %s %d deleted successfully.', MonthEnum::from($month)->getName(), $year));
        } catch (Exception $exception) {
            $this->addFlash('error', sprintf('An error occurred while deleting the wallet: %s', $exception->getMessage()));

            return $this->redirectToRoute('monthly_wallet', ['year' => $year, 'month' => $month]);
        }

        return $this->redirectToRoute('monthly_wallet', ['year' => $previousMonth['year'], 'month' => $previousMonth['month']]);
    }

    #[Route('/wallet/copy-left-to-spend/{year}/{month}', name: 'copy_left_to_spend')]
    public function copyLeftToSpend(int $year, int $month): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found');
        }

        $currentWallet = $this->walletService->getWalletByUser($user, $year, $month);
        if (!$currentWallet instanceof Wallet) {
            throw $this->createNotFoundException('Wallet not found');
        }

        try {
            $this->transactionService->copyLeftToSpendFromPreviousMonth($currentWallet);
            $this->addFlash('success', sprintf('Left to spend from previous month copied successfully for for %s %d.', MonthEnum::from($month)->getName(), $year));
        } catch (Exception $exception) {
            $this->addFlash('error', sprintf('An error occurred while copying left to spend from previous month: %s', $exception->getMessage()));

            return $this->redirectToRoute('monthly_wallet', ['year' => $year, 'month' => $month]);
        }

        return $this->redirectToRoute('monthly_wallet', ['year' => $year, 'month' => $month]);
    }
}