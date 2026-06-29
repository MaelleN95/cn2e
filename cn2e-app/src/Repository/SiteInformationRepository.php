<?php

namespace App\Repository;

use App\Entity\SiteInformation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SiteInformationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SiteInformation::class);
    }

    public function getSingleton(): SiteInformation
    {
        return $this->findOneBy([], ['id' => 'ASC']) ?? new SiteInformation();
    }
}