<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MessengerTalk;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MessengerTalk>
 */
class MessengerTalkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessengerTalk::class);
    }
}
