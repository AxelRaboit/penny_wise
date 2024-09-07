<?php

namespace App\Manager;

use App\Entity\Budget;
use Doctrine\ORM\EntityManagerInterface;

final readonly class BudgetManager
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function saveRemainingBalance(Budget $budget, float $remainingBalance): void
    {
        $budget->setRemainingBalance($remainingBalance);
        $this->entityManager->persist($budget);
        $this->entityManager->flush();
    }
}