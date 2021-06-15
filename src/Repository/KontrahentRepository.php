<?php

namespace App\Repository;

use App\Entity\Kontrahent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Kontrahent|null find($id, $lockMode = null, $lockVersion = null)
 * @method Kontrahent|null findOneBy(array $criteria, array $orderBy = null)
 * @method Kontrahent[]    findAll()
 * @method Kontrahent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KontrahentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Kontrahent::class);
    }
    public function WyszukajPoFragmencieNazwyPliku(string $fraza)
    {
        return $this->createQueryBuilder('k')
            ->where('k.nazwa LIKE :fraza')
            ->setParameter('fraza', '%'.$fraza.'%')
            ->orderBy('k.nazwa', 'ASC')
            ->getQuery()
            ->getResult()
        ;
        // 'tr.subDescription LIKE :val or myTable.mainDescription LIKE :val'
    }

    // /**
    //  * @return Kontrahent[] Returns an array of Kontrahent objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('k.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Kontrahent
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
