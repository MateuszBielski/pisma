<?php

namespace App\Repository;

use App\Entity\RodzajDokumentu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RodzajDokumentu|null find($id, $lockMode = null, $lockVersion = null)
 * @method RodzajDokumentu|null findOneBy(array $criteria, array $orderBy = null)
 * @method RodzajDokumentu[]    findAll()
 * @method RodzajDokumentu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RodzajDokumentuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RodzajDokumentu::class);
    }
    public function WyszukajPoFragmencieNazwy(string $fraza)
    {
        if(!strlen($fraza))
        {
            return $this->findAll();
        }
        return $this->createQueryBuilder('r')
            ->where('r.nazwa LIKE :fraza')
            ->setParameter('fraza', '%'.$fraza.'%')
            ->orderBy('r.nazwa', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return RodzajDokumentu[] Returns an array of RodzajDokumentu objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RodzajDokumentu
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
