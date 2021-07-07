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
    public function wyszukajPoFragmentachWyrazuOpisu(string $fragmenty)
    {
        $frArr = explode(' ',$fragmenty);
        $result = $this->createQueryBuilder('s')
        ->setParameter('frag', array_shift($frArr).'%')
        ->join("s.opis",'opis')
        ->where("opis.wartosc LIKE :frag")
        ;
        $n = 1;
        while($fr = array_shift($frArr))
        {
            $fr .='%';
            $par = "frag".$n++;
            $op = "opis".$n;
            $result = $result->setParameter("$par",$fr)
            ->join("s.opis",$op)//bez tego poniższe wyklucza wszystko
            ->andWhere("$op.wartosc LIKE :$par")
            ;
        }
        $result = $result
        ->getQuery()
        ->getResult();
        ;

         return $result;
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
