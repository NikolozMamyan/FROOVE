<?php

namespace App\Repository;

use App\Entity\Ads;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AdsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ads::class);
    }

    /**
     * Retourne les annonces filtrées selon les paramètres.
     * On reçoit un tableau d'options (catégorie, location, currency).
     */
    public function findFilteredAds(?string $category, ?string $location, ?string $currency): array
    {
        $qb = $this->createQueryBuilder('a');

        if ($category && $category !== 'Catégorie') {
            $qb->andWhere('a.category = :category')
               ->setParameter('category', $category);
        }

        if ($location) {
            $qb->andWhere('a.location LIKE :location')
               ->setParameter('location', '%'.$location.'%');
        }

        if ($currency && $currency !== 'Devise') {
            $qb->andWhere('a.currency = :currency')
               ->setParameter('currency', $currency);
        }

        // On peut trier par date, par exemple :
        $qb->orderBy('a.date', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
