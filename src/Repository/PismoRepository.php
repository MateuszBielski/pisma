<?php

namespace App\Repository;

use App\Entity\Kontrahent;
use App\Entity\Pismo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Pismo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pismo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pismo[]    findAll()
 * @method Pismo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PismoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pismo::class);
    }

    public function findWszystkiePismaKontrahenta(Kontrahent $k)
    {
        return $this->createQueryBuilder('p')
            ->where('p.nadawca = :k_id')
            ->orWhere('p.odbiorca = :k_id')
            ->setParameter('k_id', $k->getId())
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Pismo[] Returns an array of Pismo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Pismo
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
