<?php

namespace App\Repository;

use App\Entity\Order;
use App\Enum\OrderStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    // CA par mois (commandes livrées only)
    public function getTotalSalesByMonth(): array
    {
        return $this->createQueryBuilder('o')
            ->select("SUBSTRING(o.createdAt, 1, 7) as month, SUM(oi.quantity * oi.productPrice) as totalSales")
            ->leftJoin('o.orderItem', 'oi')
            ->where('o.status = :status')
            ->setParameter('status', OrderStatus::DELIVERED)
            ->groupBy('month')
            ->orderBy('month', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
