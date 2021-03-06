<?php

namespace App\Repository;

use App\Entity\Kontrahent;
use App\Entity\Pismo;
use DateTimeInterface;
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
            ->orderBy('p.dataDokumentu', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
    public function WyszukajPoFragmencieNazwyPliku(string $fraza)
    {
        return $this->createQueryBuilder('p')
            ->where('p.nazwaPliku LIKE :fraza')
            ->setParameter('fraza', '%'.$fraza.'%')
            ->orderBy('p.dataDokumentu', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
    public function WyszukajPoFragmentachOpisuKontrahIsprawy(string $pismo,string $sprawa,string $kontrahent,string $pocz,string $kon)
    {
        $frArrP = explode(' ',$pismo);
        $frArrS = ($sprawa != '') ? explode(' ',$sprawa):[];
        $frArrK = ($kontrahent != '') ? explode(' ',$kontrahent): [];
        

        $result = $this->createQueryBuilder('p')
        ->setParameter('frag', array_shift($frArrP).'%')
        ->join("p.opis",'opis')
        ->where("opis.wartosc LIKE :frag")
        ;
        $n = 1;
        while($fr = array_shift($frArrP))
        {
            $fr .='%';
            $par = "frag".$n++;
            $op = "opis".$n;
            $result = $result->setParameter("$par",$fr)
            ->join("p.opis",$op)//bez tego poni??sze wyklucza wszystko
            ->andWhere("$op.wartosc LIKE :$par")
            ;
        }
        $n = 1;
        if(count($frArrS))
        {
            $result = $result
            ->join("p.sprawy",'sprawa');
            while($fr = array_shift($frArrS))
            {
                $fr .='%';
                $par = "fragS".$n++;
                $op = "opisS".$n;
                $result = $result->setParameter("$par",$fr)
                ->join("sprawa.opis",$op)//bez tego poni??sze wyklucza wszystko
                ->andWhere("$op.wartosc LIKE :$par")
                ;
            }
        }
        $n = 1;
        if(count($frArrK))
        {
            $result = $result
            // ->join("p.odbiorca",'odbiorca')
            ->leftJoin("p.nadawca",'nadawca')
            ->leftjoin("p.odbiorca",'odbiorca')
            ;
            while($fr = array_shift($frArrK))
            {
                $fr ='%'.$fr.'%';
                $par = "fragK".$n++;
                $result = $result->setParameter("$par",$fr)
                ->andWhere("nadawca.nazwa LIKE :$par or odbiorca.nazwa LIKE :$par")//
                
                ;
            }
        }
        if(strlen($pocz) && strlen($kon))
        {
            $result = $result
            ->setParameter('pocz',$pocz)
            ->setParameter('kon',$kon)
            ->andWhere("p.dataDokumentu BETWEEN :pocz AND :kon");    
        }
        $result = $result
        ->orderBy('p.oznaczenie', 'DESC')
        ->addOrderBy('p.dataDokumentu', 'DESC')
        ->getQuery()
        ->getResult();
        ;

         return $result;
    }
    public function OstatniNumerPrzychodzacych()
    {
        return $this->createQueryBuilder('p')
        ->where('p.oznaczenie IS NOT NULL')
        ->andWhere('p.nadawca IS NOT NULL')
        ->orderBy('p.oznaczenie', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
    }
    public function OstatniNumerWychodzacych()
    {
        return $this->createQueryBuilder('p')
        ->where('p.oznaczenie IS NOT NULL')
        ->andWhere('p.odbiorca IS NOT NULL')
        ->orderBy('p.oznaczenie', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
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
