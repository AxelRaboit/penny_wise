<?php

namespace App\Repository;

use App\Entity\Link;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Link>
 */
class LinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Link::class);
    }

    /**
     * Finds all links associated with a specific individual.
     *
     * @param User $individual The individual to filter links by
     *
     * @return Link[] Returns an array of Link objects
     */
    public function findByIndividual(User $individual): array
    {
        /** @var Link[] $links */
        $links = $this->createQueryBuilder('l')
            ->where('l.individual = :individual')
            ->setParameter('individual', $individual)
            ->orderBy('l.id', Order::Descending->value)
            ->getQuery()
            ->getResult();

        return $links;
    }
}
