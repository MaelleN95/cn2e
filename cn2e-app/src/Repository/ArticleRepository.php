<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Actualités récentes (moins de 1 an)
     */
    public function findRecentes(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.publishedAt >= :date')
            ->setParameter('date', new \DateTime('-1 year'))
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Actualités archivées (plus de 1 an)
     */
    public function findArchiveesGroupedByYear(): array
    {
        $results = $this->createQueryBuilder('a')
            ->where('a.publishedAt < :date')
            ->setParameter('date', new \DateTimeImmutable('-1 year'))
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();

        $grouped = [];

        foreach ($results as $actualite) {
            $year = $actualite->getPublishedAt()->format('Y');

            if (!isset($grouped[$year])) {
                $grouped[$year] = [];
            }

            $grouped[$year][] = $actualite;
        }

        return $grouped;
    }

    /**
     * Nombre d’archives (optimisé)
     */
    public function countArchivees(): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.publishedAt < :date')
            ->setParameter('date', new \DateTime('-1 year'))
            ->getQuery()
            ->getSingleScalarResult();
    }
}
