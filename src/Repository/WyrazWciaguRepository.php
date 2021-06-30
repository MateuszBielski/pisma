<?php

namespace App\Repository;

use App\Entity\WyrazWciagu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method WyrazWciagu|null find($id, $lockMode = null, $lockVersion = null)
 * @method WyrazWciagu|null findOneBy(array $criteria, array $orderBy = null)
 * @method WyrazWciagu[]    findAll()
 * @method WyrazWciagu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WyrazWciaguRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WyrazWciagu::class);
    }

    // /**
    //  * @return WyrazWciagu[] Returns an array of WyrazWciagu objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?WyrazWciagu
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
