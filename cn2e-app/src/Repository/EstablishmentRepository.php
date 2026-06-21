<?php

namespace App\Repository;

use App\Entity\Establishment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Establishment>
 */
class EstablishmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Establishment::class);
    }

    public function search(?string $search): array
    {
        $qb = $this->createQueryBuilder('e');

        if ($search) {
            $search = mb_strtolower($search);

            $qb->andWhere(
                $qb->expr()->orX(
                    'e.name LIKE :search',
                    'e.city LIKE :search',
                    'e.region LIKE :search',
                    'e.academy LIKE :search'
                )
            )
            ->setParameter('search', '%' . $search . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
