<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionForBudgetType;
use App\Form\TransactionType;
use App\Repository\BudgetRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TransactionController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly BudgetRepository $budgetRepository, private readonly TransactionRepository $transactionRepository){}

    #[Route('/transaction/new', name: 'transaction_new')]
    public function new(Request $request): Response
    {
        $transaction = new Transaction();
        $form = $this->createForm(TransactionType::class, $transaction);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($transaction);
            $this->entityManager->flush();

            return $this->redirectToRoute('monthly_budget', [
                'year' => $transaction->getBudget()->getYear(),
                'month' => $transaction->getBudget()->getMonth(),
            ]);
        }

        return $this->render('transaction/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/transaction/new/{year}/{month}', name: 'transaction_new_for_budget')]
    public function newForBudget(int $year, int $month, Request $request): Response
    {
        $budget = $this->budgetRepository
            ->findOneBy(['year' => $year, 'month' => $month]);

        if (!$budget) {
            throw $this->createNotFoundException('Budget not found for the specified year and month.');
        }

        $transaction = new Transaction();
        $transaction->setBudget($budget);

        $form = $this->createForm(TransactionForBudgetType::class, $transaction, [
            'budget' => $budget,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($transaction);
            $this->entityManager->flush();

            return $this->redirectToRoute('monthly_budget', [
                'year' => $year,
                'month' => $month,
            ]);
        }

        return $this->render('transaction/new_for_budget.html.twig', [
            'form' => $form,
            'budget' => $budget,
        ]);
    }

    #[Route('/transaction/delete/{year}/{month}/{id}', name: 'transaction_delete')]
    public function delete(int $year, int $month, int $id, Request $request): RedirectResponse
    {
        $transaction = $this->transactionRepository->find($id);
        if (!$transaction) {
            throw $this->createNotFoundException('Transaction not found.');
        }

        try {
            $this->entityManager->remove($transaction);
            $this->entityManager->flush();
            $this->addFlash('success', 'Transaction deleted successfully.');
        } catch (Exception $exception) {
            $this->addFlash('error', sprintf('Error deleting transaction: %s', $exception->getMessage()));
            return $this->redirectToRoute('monthly_budget', [
                'year' => $year,
                'month' => $month,
            ]);
        }

        return $this->redirectToRoute('monthly_budget', [
            'year' => $year,
            'month' => $month,
        ]);
    }
}
