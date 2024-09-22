<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\TransactionTag;
use App\Entity\User;
use App\Form\TransactionTagType;
use App\Manager\TransactionTagManager;
use App\Repository\TransactionTagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TransactionTagController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly TransactionTagManager $transactionTagManager) {}

    #[Route('/transaction-tag/new', name: 'transaction_tag_new')]
    public function new(Request $request): Response
    {
        $transactionTag = new TransactionTag();

        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedException('You must be logged in to create a tag.');
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
            throw new AccessDeniedException('You must be logged in to view tags.');
        }

        $transactionTags = $transactionTagRepository->findByUser($user);

        return $this->render('transaction_tag/list.html.twig', [
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
