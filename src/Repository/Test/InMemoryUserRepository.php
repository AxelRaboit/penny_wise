<?php

declare(strict_types=1);

namespace App\Repository\Test;

use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final readonly class InMemoryUserRepository
{
    /**
     * @var Collection<int, User>
     */
    private Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function save(User $user): void
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }
    }

    /**
     * @param array<string, mixed> $criteria
     */
    public function findOneBy(array $criteria): ?User
    {
        foreach ($this->users as $user) {
            if (isset($criteria['email']) && $user->getEmail() === $criteria['email']) {
                return $user;
            }
        }

        return null;
    }

    /**
     * @return Collection<int, User>
     */
    public function findAll(): Collection
    {
        return $this->users;
    }

    public function delete(User $user): void
    {
        $this->users->removeElement($user);
    }
}
