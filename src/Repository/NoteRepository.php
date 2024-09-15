<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Wallet;
use App\Entity\Note;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Note>
 */
class NoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }

    /**
     * Retrieves notes associated with a specific wallet.
     *
     * @return Note[] Returns an array of Note objects
     */
    public function getNotesFromWallet(Wallet $wallet): array
    {
        /** @var Note[] $notes */
        $notes = $this->createQueryBuilder('n')
            ->where('n.wallet = :wallet')
            ->setParameter('wallet', $wallet)
            ->orderBy('n.id', Order::Descending->value)
            ->getQuery()
            ->getResult();

        return $notes;
    }
}
