<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    // nb produits par statut
    public function getStockRatio(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.status, COUNT(p.id) as count')
            ->groupBy('p.status')
            ->getQuery()
            ->getResult();
    }

    // recherche produits (avec images + catégories)
    public function searchByName(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.image', 'i')
            ->addSelect('i')
            ->leftJoin('p.category', 'c')
            ->addSelect('c')
            ->where('p.name LIKE :query OR p.description LIKE :query OR c.name LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
