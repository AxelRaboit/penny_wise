<?php

declare(strict_types=1);

namespace App\Controller\TransactionTag;

use App\Entity\TransactionTag;
use App\Entity\User;
use App\Form\Transaction\TransactionTagType;
use App\Manager\Refacto\TransactionTag\TransactionTagManager;
use App\Repository\Transaction\TransactionTagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TransactionTagController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly TransactionTagManager $transactionTagManager) {}

    // TODO AXEL: i am cleaning all the controllers, and i am at this point, so i need to clean this controller, use services etc...
    // TODO AXEL: See if i need to change route path /account....
    // TODO AXEL: This controller has been refactored, but not completely, so i need to keep going on it
    // TODO AXEL: use services, checkers, voters, managers, etc.

    // TODO AXEL: This controller has been refactored, but not completely, so i need to keep going on i
    // TODO AXEL: I need to make another pass on all the controllers, to check route paths and name, and also template names.

    #[Route('/transaction-tag/new', name: 'transaction_tag_new')]
    public function new(Request $request): Response
    {
        $transactionTag = new TransactionTag();

        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('You must be logged in to create a tag.');
        }

        $transactionTag->setUser($user);

        $form = $this->createForm(TransactionTagType::class, $transactionTag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($transactionTag);
            $this->entityManager->flush();

            return $this->redirectToRoute('transaction_tag_list');
        }

        return $this->render('transaction_tag/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/transaction-tags', name: 'transaction_tag_list')]
    public function list(TransactionTagRepository $transactionTagRepository): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('You must be logged in to view tags.');
        }

        $transactionTags = $transactionTagRepository->findByUser($user);

        return $this->render('transaction/transaction_tag/list.html.twig', [
            'transactionTags' => $transactionTags,
        ]);
    }

    #[Route('/transaction-tag/{id}/edit', name: 'transaction_tag_edit')]
    public function edit(Request $request, TransactionTag $transactionTag): Response
    {
        $form = $this->createForm(TransactionTagType::class, $transactionTag);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('transaction_tag_list');
        }

        return $this->render('transaction_tag/edit.html.twig', [
            'form' => $form,
            'transactionTag' => $transactionTag,
        ]);
    }

    #[Route('/transaction-tag/{id}/delete', name: 'transaction_tag_delete')]
    public function delete(TransactionTag $transactionTag): Response
    {
        try {
            $this->transactionTagManager->deleteTransactionTag($transactionTag);
            $this->addFlash('success', 'Transaction tag deleted successfully.');
        } catch (Exception $exception) {
            $this->addFlash('error', sprintf('An error occurred while deleting the tag: %s', $exception->getMessage()));
        }

        return $this->redirectToRoute('transaction_tag_list');
    }
}
