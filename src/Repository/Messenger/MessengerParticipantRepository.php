<?php

declare(strict_types=1);

namespace App\Repository\Messenger;

use App\Entity\MessengerParticipant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MessengerParticipant>
 */
class MessengerParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessengerParticipant::class);
    }
}
