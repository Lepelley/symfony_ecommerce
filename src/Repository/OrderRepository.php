<?php

namespace App\Repository;

use App\Entity\Basket;
use App\Entity\Order;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    // /**
    //  * @return Order[] Returns an array of Order objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Order
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findOrderProduct(Basket $basket, Product $product)
    {
//        $order = $this
//            ->createQueryBuilder('order')
//            ->where('order.basket = :basket')
//            ->setParameter('basket', $basket)
//            ->andWhere('order.product = :product')
//            ->setParameter('product', $product)
//            ->setMaxResults(1)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;

        $order = $this->findOneBy(['basket' => $basket, 'product' => $product]);

        if (null !== $order) {
            return $order;
        }

        return (new Order())
            ->setBasket($basket)
            ->setQuantity(0)
            ->setProduct($product)
        ;
    }
}
