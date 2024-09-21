<?php

declare(strict_types=1);

namespace App\Repository\Test;

use App\Entity\Wallet;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final readonly class InMemoryWalletRepository
{
    /**
     * @var Collection<int, Wallet>
     */
    private Collection $wallets;

    public function __construct()
    {
        $this->wallets = new ArrayCollection();
    }

    public function save(Wallet $wallet): void
    {
        if (!$this->wallets->contains($wallet)) {
            $this->wallets->add($wallet);
        }
    }

    /**
     * Finds a Wallet by criteria.
     *
     * @param array<string, mixed> $criteria
     */
    public function findOneBy(array $criteria): ?Wallet
    {
        foreach ($this->wallets as $wallet) {
            if (isset($criteria['id']) && $wallet->getId() !== $criteria['id']) {
                continue;
            }

            if (isset($criteria['year']) && $wallet->getYear() !== $criteria['year']) {
                continue;
            }

            if (isset($criteria['month']) && $wallet->getMonth() !== $criteria['month']) {
                continue;
            }

            if (isset($criteria['startDate']) && $wallet->getStartDate() instanceof DateTimeInterface
                && $criteria['startDate'] instanceof DateTimeInterface
                && $wallet->getStartDate()->format('Y-m-d') !== $criteria['startDate']->format('Y-m-d')) {
                continue;
            }

            if (isset($criteria['endDate']) && $wallet->getEndDate() instanceof DateTimeInterface
                && $criteria['endDate'] instanceof DateTimeInterface
                && $wallet->getEndDate()->format('Y-m-d') !== $criteria['endDate']->format('Y-m-d')) {
                continue;
            }

            return $wallet;
        }

        return null;
    }

    /**
     * Finds all wallets.
     *
     * @return Collection<int, Wallet>
     */
    public function findAll(): Collection
    {
        return $this->wallets;
    }

    public function delete(Wallet $wallet): void
    {
        $this->wallets->removeElement($wallet);
    }
}
