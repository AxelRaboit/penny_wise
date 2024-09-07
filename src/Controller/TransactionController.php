<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionType;
use App\Service\BudgetService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TransactionController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager){}

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
            'form' => $form->createView(),
        ]);
    }
}
