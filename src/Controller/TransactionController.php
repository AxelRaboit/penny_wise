<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionType;
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

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'status' => 'form',
                'form' => $this->renderView('transaction/new.html.twig', [
                    'form' => $form->createView(),
                ])
            ]);
        }

        return $this->render('transaction/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/transaction/new/submission', name: 'transaction_new_submission')]
    public function newSubmission(Request $request): Response
    {
        $transaction = new Transaction();
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($transaction);

            try {
                $this->entityManager->flush();
                if ($request->isXmlHttpRequest()) {
                    return $this->json(['status' => 'success']);
                } else {
                    return $this->redirectToRoute('app_homepage');
                }
            } catch (\Exception $e) {
                if ($request->isXmlHttpRequest()) {
                    return $this->json(['status' => 'error', 'message' => $e->getMessage()]);
                } else {
                    $this->addFlash('error', 'An error occurred while saving the transaction: ' . $e->getMessage());
                    return $this->render('transaction/new.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
            }
        }

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'status' => 'form',
                'form' => $this->renderView('transaction/new.html.twig', [
                    'form' => $form->createView(),
                ])
            ]);
        }

        return $this->render('transaction/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
