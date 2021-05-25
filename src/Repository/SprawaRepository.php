<?php

namespace App\Repository;

use App\Entity\Sprawa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sprawa|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sprawa|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sprawa[]    findAll()
 * @method Sprawa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SprawaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sprawa::class);
    }

    // /**
    //  * @return Sprawa[] Returns an array of Sprawa objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Sprawa
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
