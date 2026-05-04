<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findUpcoming(): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.startDate >= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('e.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPast(): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.startDate < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('e.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function isPast(Event $event): bool
    {
        return $event->getStartDate() < new \DateTimeImmutable();
    }
}
